#!groovy
@Library('aereus.pipeline') _

import aereus.pipeline.CodeQualityReporter
import aereus.pipeline.DeploymentTargets
import groovy.json.JsonSlurper
def APPLICATION_VERSION = "v" + env.BUILD_NUMBER
def DOCKERHUB_SERVER = "dockerhub.aereusdev.com"
def dockerImage;
def clientImage;
currentBuild.result = "SUCCESS"

pipeline {
    agent { node { label 'linux' } }
    stages {
        stage('Build') {
            steps {
                script {
                    sh 'env'
                    checkout scm
                    docker.withRegistry("https://${DOCKERHUB_SERVER}", 'aereusdev-dockerhub') {
                        /* If this is the master branch, punlish to stable, if it is develop publish to latest */
                        clientImage = docker.image("${DOCKERHUB_SERVER}/netric-client-web:latest")
                        clientImage.pull()
                    }

                    /* Get the built client from netric.client.web container and copy to the local mounted server/mobile directory */
                    clientImage.inside {
                        sh 'cp -r /var/www/app/build/* ./public/mobile/'
                    }

                    dockerImage = docker.build("${DOCKERHUB_SERVER}/netric:${APPLICATION_VERSION}");
                }
            }
        }

        stage('Test') {
            steps {
                // script {
                //     sh 'docker-compose -f docker/docker-compose-test.yml up --exit-code-from netric_server'

                //     // Report on junit
                //     junit 'tests/tmp/junit.xml'

                //     // Create style and static analysis reports
                //     // sh 'docker exec docker_netric_server_1 composer lint-phpcs || true'
                //     // sh 'docker exec docker_netric_server_1 composer lint-phpmd || true'

                //     // Send reports to server for code quality metrics
                //     def reporter = new CodeQualityReporter([
                //         cloverFilePath: readFile("tests/tmp/clover.xml"),
                //         checkStyleFilePath: readFile("tests/tmp/checkstyle.xml"),
                //         pmdFilePath: readFile("tests/tmp/pmd.xml")
                //     ])
                //     reporter.collectAndSendReport('netric.com')
                // }
                script {
                    dir('.clair') {
                        def nodeIp = sh(
                            script: "ip addr show dev eth0  | grep 'inet ' | sed -e 's/^[ \t]*//' | cut -d ' ' -f 2 | cut -d '/' -f 1",
                            returnStdout: true
                        ).trim();
                        git branch: 'master',
                            credentialsId: '9862b4cf-a692-43c5-9614-9d93114f93a7',
                            url: 'ssh://git@src.aereusdev.com/source/clair.aereusdev.com.git'

                            sh 'chmod +x ./bin/clair-scanner_linux_amd64'

                            // Fail if any critical security vulnerabilities are found
                            sh "./bin/clair-scanner_linux_amd64 -t 'Critical' -c http://192.168.1.25:6060 --ip=${nodeIp} ${DOCKERHUB_SERVER}/netric"
                    }
                }
            }
        }

        stage('Publish') {
            steps {
                script {
                    docker.withRegistry('https://dockerhub.aereusdev.com', 'aereusdev-dockerhub') {
                        dockerImage.push()
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
                        sh "ssh -p 222 -o StrictHostKeyChecking=no aereus@dev1.aereusdev.com /home/aereus/deploy.sh integration ${APPLICATION_VERSION}"
                    }
                }
                // Wait for the upgrade to finish
                script {
                    getDeployStatus(
                        environment: DeploymentTargets.INTEGRATION,
                        serviceName: 'netric_com_netric',
                        imageTag: "${APPLICATION_VERSION}"
                    )
                }
            }
        }

        stage('Production') {
            steps {
                // Call stack deploy to upgrade
                script {
                        def server = 'aereus@web2.aereus.com';

                        sshagent (credentials: ['aereus']) {

                        sh 'scp scripts/deploy.sh ${server}:/home/aereus/deploy.sh'
                        sh 'scp docker/docker-compose-stack.yml ${server}:/home/aereus/docker-compose-stack.yml'
                        sh 'ssh ${server} chmod +x /home/aereus/deploy.sh'
                        sh "ssh ${server} /home/aereus/deploy.sh production ${APPLICATION_VERSION}"
                    }

                    // Wait for the upgrade to finish
                    getDeployStatus(
                        environment: DeploymentTargets.PRODUCTION_PRESENTATION_DALLAS,
                        serviceName: 'netric_com_netric',
                        imageTag: "${APPLICATION_VERSION}"
                    )
                }
            }
        }
    }
    post {
        always {
            // Shutdown
            sh 'docker-compose -f docker/docker-compose-test.yml down'
            cleanWs()
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
