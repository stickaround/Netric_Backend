#!groovy
@Library('aereus.pipeline') _

import aereus.pipeline.CodeQualityReporter
import aereus.pipeline.DeploymentTargets
import aereus.pipeline.SwarmServiceInspector
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
                script {
                    echo 'Skipping test to verify deployment to production'
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
                    verifyDeploySuccess(
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
                    def server = 'aereus@web2.aereus.com -o StrictHostKeyChecking=no';

                    sshagent (credentials: ['aereus']) {
                        sh "scp scripts/deploy.sh ${server}:/home/aereus/deploy.sh"
                        sh "scp docker/docker-compose-stack.yml ${server}:/home/aereus/docker-compose-stack.yml"
                        sh "ssh ${server} chmod +x /home/aereus/deploy.sh"
                        sh "ssh ${server} /home/aereus/deploy.sh production ${APPLICATION_VERSION}"
                    }

                    // Wait for the upgrade to finish
                    verifyDeploySuccess(
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
