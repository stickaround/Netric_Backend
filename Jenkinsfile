#!groovy
@Library('aereus.pipeline') _

import aereus.pipeline.CodeQualityReporter
import groovy.json.JsonSlurper;
def dockerImage;
def clientImage;
currentBuild.result = "SUCCESS"

pipeline {
    agent { node { label 'linux' } }
    stages {
        stage('Build') {
            steps {
                script {
                    sh 'printenv'
                    checkout scm
                    docker.withRegistry('https://dockerhub.aereusdev.com', 'aereusdev-dockerhub') {
                        /* If this is the master branch, punlish to stable, if it is develop publish to latest */
                        if (env.BRANCH_NAME == 'master') {
                            clientImage = docker.image("dockerhub.aereusdev.com/netric-client-web:stable")
                        } else {
                            clientImage = docker.image("dockerhub.aereusdev.com/netric-client-web:latest")
                        }

                        clientImage.pull()
                    }

                    /* Get the built client from netric.client.web container and copy to the local mounted server/mobile directory */
                    clientImage.inside {
                        sh 'cp -r /var/www/app/build/* ./public/mobile/'
                    }

                    dockerImage = docker.build('netric');
                }
            }
        }

        stage('Test') {
            steps {
                script {
                    sh 'docker-compose -f docker/docker-compose-test.yml pull'
                    sh 'docker-compose -f docker/docker-compose-test.yml build'
                    sh 'docker-compose -f docker/docker-compose-test.yml up -d'
                    sleep 120
                    // Manually running netric-setup.sh' should not be needed any more
                    // since it it handled in the startup of containers
                    //sh 'docker exec docker_netric_server_1 /netric-setup.sh'
                    sh 'docker exec docker_netric_server_1 /netric-tests.sh'

                    // Create style and static analysis reports
                    sh 'docker exec docker_netric_server_1 composer lint-phpcs || true'
                    sh 'docker exec docker_netric_server_1 composer lint-phpmd || true'
                    

                    sh 'docker-compose -f docker/docker-compose-test.yml down'
                    junit 'tests/tmp/logfile.xml'

                    // Send reports to server for code quality metrics
                    def reporter = new CodeQualityReporter([
                        cloverFilePath: readFile("tests/tmp/clover.xml"),
                        checkStyleFilePath: readFile("tests/tmp/checkstyle.xml"),
                        pmdFilePath: readFile("tests/tmp/pmd.xml")
                    ])
                    reporter.collectAndSendReport('netric.com')
                }
                script {
                    dir('.clair') {
                        def nodeIp = sh (
                            script: "ip addr show dev eth0  | grep 'inet ' | sed -e 's/^[ \t]*//' | cut -d ' ' -f 2 | cut -d '/' -f 1",
                            returnStdout: true
                        ).trim();
                        git branch: 'master',
                            credentialsId: '9862b4cf-a692-43c5-9614-9d93114f93a7',
                            url: 'ssh://git@src.aereusdev.com/source/clair.aereusdev.com.git'

                            sh 'chmod +x ./bin/clair-scanner_linux_amd64'

                            // Fail if any critical security vulnerabilities are found
                            sh "./bin/clair-scanner_linux_amd64 -t 'Critical' -c http://192.168.1.25:6060 --ip=${nodeIp} netric"
                    }
                }
            }
        }

        stage('Publish') {
            steps {
                script {
                    docker.withRegistry('https://dockerhub.aereusdev.com', 'aereusdev-dockerhub') {
                        dockerImage.push("v${env.BUILD_NUMBER}")
                    }
                }
            }
        }

        stage('Integration') {
            steps {
                // Call stack deploy to upgrade
                script {
                    sshagent (credentials: ['aereus']) {
                        sh 'scp -P 222 -o StrictHostKeyChecking=no scripts/deploy.sh aereus@dev1.aereusdev.com:/home/aereus/deploy.sh'
                        sh 'scp -P 222 -o StrictHostKeyChecking=no docker/docker-compose-stack.yml aereus@dev1.aereusdev.com:/home/aereus/docker-compose-stack.yml'
                        sh 'ssh -p 222 -o StrictHostKeyChecking=no aereus@dev1.aereusdev.com chmod +x /home/aereus/deploy.sh'
                        sh "ssh -p 222 -o StrictHostKeyChecking=no aereus@dev1.aereusdev.com /home/aereus/deploy.sh integration ${env.BUILD_NUMBER}"
                    }
                }
                // Wait for the upgrade to finish
                script {
                    timeout(5) {
                        waitUntil {
                            sshagent (credentials: ['aereus']) {
                                def jsonText  = sh(returnStdout: true, script: 'ssh -p 222  -o StrictHostKeyChecking=no  aereus@dev1.aereusdev.com -C "docker service inspect netric_com_netric"').trim()
                                def jsonData = new JsonSlurper().parseText(jsonText)

                                // Look for a failure/rollback exit
                                if(jsonData[0].UpdateStatus.State == 'paused') {
                                    println("Deploy Failed:")
                                    // Send direct link to make it easier
                                    println("https://logs.aereusdev.com/app/kibana#/discover?_g=()&_a=(columns:!(_source),filters:!(('\$state':(store:appState),meta:(alias:!n,disabled:!f,index:'logstash-*',key:app_ver,negate:!f,value:${env.BUILD_NUMBER}),query:(match:(app_ver:(query:${env.BUILD_NUMBER},type:phrase))))),index:'logstash-*'")
                                    println("---------------------------------")
                                    print(jsonData[0].UpdateStatus.Message)
                                    println("---------------------------------")
                                    
                                    currentBuild.result = "SUCCESS"
                                }

                                return (jsonData[0].UpdateStatus.State == 'completed')
                            }
                        }
                    }
                }
            }
        }

        stage('Production') {
            steps {
                script {
                    sshagent (credentials: ['aereus']) {
                        // Run Setup First
                        sh 'scp -o StrictHostKeyChecking=no scripts/pull-and-run-setup.sh aereus@web1.aereus.com:/home/aereus/pull-and-run-setup.sh'
                        // sh 'ssh -o StrictHostKeyChecking=no aereus@web1.aereus.com chmod +x /home/aereus/pull-and-run-setup.sh'
                        // sh 'ssh -o StrictHostKeyChecking=no aereus@web1.aereus.com /home/aereus/pull-and-run-setup.sh production'
                        // sh 'ssh -o StrictHostKeyChecking=no aereus@web1.aereus.com rm /home/aereus/pull-and-run-setup.sh'

                        // Now Run the Daemon
                        // Do not run for now - we are switching to docker swarm
                        // sh 'scp -o StrictHostKeyChecking=no scripts/pull-and-run-daemon.sh aereus@db2.aereus.com:/home/aereus/pull-and-run-daemon.sh'
                        // sh 'ssh -o StrictHostKeyChecking=no aereus@db2.aereus.com chmod +x /home/aereus/pull-and-run-daemon.sh'
                        // sh 'ssh -o StrictHostKeyChecking=no aereus@db2.aereus.com /home/aereus/pull-and-run-daemon.sh latest'
                        // sh 'ssh -o StrictHostKeyChecking=no aereus@db2.aereus.com rm /home/aereus/pull-and-run-daemon.sh'
                    }
                }
            }
        }
    }
    post {
        always {
            sh 'docker system prune -af'
        }
        failure {
            emailext (
                subject: "FAILED: Job '${env.JOB_NAME} [${env.BUILD_NUMBER}]'",
                body: """<p>FAILED: Job '${env.JOB_NAME} [${env.BUILD_NUMBER}]':</p>
                    <p>Check console output at &QUOT;<a href='${env.BUILD_URL}'>${env.JOB_NAME} [${env.BUILD_NUMBER}]</a>&QUOT;</p>""",
                recipientProviders: [[$class: 'DevelopersRecipientProvider']]
            )
        }
    }
}
