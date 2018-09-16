#!groovy
@Library('aereus.pipeline') _

import aereus.pipeline.CodeQualityReporter
import aereus.pipeline.DeploymentTargets
import aereus.pipeline.SwarmServiceInspector
import groovy.json.JsonSlurper
def APPLICATION_VERSION = "v" + env.BUILD_NUMBER
def DOCKERHUB_SERVER = "dockerhub.aereusdev.com"
def PROJECT_NAME = 'netric_com'
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
                script {
                    sh 'docker-compose -f docker/docker-compose-test.yml up --exit-code-from netric_server'
                    //sh 'docker-compose -f docker/docker-compose-test.yml up -d'
                    //sh 'docker-compose -f docker/docker-compose-test.yml exec -T netric_server /netric-setup.sh'
                    //sh 'docker-compose -f docker/docker-compose-test.yml exec -T netric_server /netric-tests.sh'

                    // Report on junit
                    junit 'tests/tmp/junit.xml'

                    // Send reports to server for code quality metrics
                    codeQualityReport(
                       repositoryName: 'netric.com',
                       teamName: 'Netric',
                       cloverFile: 'tests/tmp/clover.xml',
                       pmdFile: 'tests/tmp/pmd.xml',
                       checkStyleFile: 'tests/tmp/checkstyle.xml'
                    )
                }
                script {
                    // Check container for security vulnerabilities
                    dir('.clair') {
                        def nodeIp = sh(
                            script: "ip addr show dev eth0  | grep 'inet ' | sed -e 's/^[ \t]*//' | cut -d ' ' -f 2 | cut -d '/' -f 1",
                            returnStdout: true
                        ).trim();

                        // Pull the clairscanner binary
                        git branch: 'master',
                            credentialsId: '9862b4cf-a692-43c5-9614-9d93114f93a7',
                            url: 'ssh://git@src.aereusdev.com/source/clair.aereusdev.com.git'

                        sh 'chmod +x ./bin/clair-scanner_linux_amd64'

                        // Fail if any critical security vulnerabilities are found
                        sh "./bin/clair-scanner_linux_amd64 -t 'Critical' -c http://192.168.1.25:6060 --ip=${nodeIp} ${DOCKERHUB_SERVER}/netric:${APPLICATION_VERSION}"
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

        stage('Integration Setup') {
            steps {
                // Call stack deploy to upgrade
                script {
                    sshagent (credentials: ['aereus']) {
                        sh "ssh -p 222 -o StrictHostKeyChecking=no aereus@dev1.aereusdev.com docker run -i --rm -e 'APPLICATION_ENV=integration' -e 'APPLICATION_VER=${APPLICATION_VERSION}' --entrypoint='/netric-setup.sh' dockerhub.aereusdev.com/netric:${APPLICATION_VERSION}"
                    }
                }
            }
        }

        stage('Integration') {
            steps {
                script {
                    print 'Skipping for now'
                    //deployToSwarm(
                    //    environment: DeploymentTargets.INTEGRATION,
                    //    stackName: PROJECT_NAME,
                    //    imageTag: APPLICATION_VERSION,
                    //    serviceDomain: '*.integ.netric.com'
                    //)
                }
            }
        }

        stage('Production Setup') {
            steps {
                // Call stack deploy to upgrade
                script {
                    sshagent (credentials: ['aereus']) {
                        sh "ssh -o StrictHostKeyChecking=no aereus@web2.aereus.com docker run -i --rm -e 'APPLICATION_ENV=production' -e 'APPLICATION_VER=${APPLICATION_VERSION}' --entrypoint='/netric-setup.sh' dockerhub.aereusdev.com/netric:${APPLICATION_VERSION}"
                    }
                }
            }
        }

        stage('Production') {
            steps {
                // Call stack deploy to upgrade
                script {
                    script {
                        deployToSwarm(
                            environment: DeploymentTargets.PRODUCTION_PRESENTATION_DALLAS,
                            stackName: PROJECT_NAME,
                            imageTag: APPLICATION_VERSION,
                            serviceDomain: '*.netric.com'
                        )
                    }
                }
            }
        }
    }
    post {
        always {
            sh 'docker-compose -f docker/docker-compose-test.yml down'
            cleanWs()
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
