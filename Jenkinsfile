#!groovy
@Library('aereus.pipeline') _

import aereus.pipeline.CodeQualityReporter
import aereus.pipeline.DeploymentTargets
import aereus.pipeline.PublishTargets
import aereus.pipeline.SwarmServiceInspector
import groovy.json.JsonSlurper
def APPLICATION_VERSION = "v" + env.BUILD_NUMBER
def DOCKERHUB_SERVER = PublishTargets.DOCKERHUB
def PROJECT_NAME = 'netric'
def dockerImage;
def clientImage;
currentBuild.result = "SUCCESS"

pipeline {
    agent { node { label 'linux' } }
    options {
        buildDiscarder(logRotator(numToKeepStr:'10'))
        //timeout(time: 60, unit: 'MINUTES')
        ansiColor('xterm')
    }
    parameters {
        string(
            defaultValue: '',
            description: 'Differential ID for use with a review - will not release',
            name: 'DIFF_ID'
        )
        string(
            defaultValue: '',
            description: 'Phabricator user ID used when submitting a review',
            name: 'PHID'
        )
    }
    stages {
        stage('Build') {
            steps {
                script {
                    sh 'env'
                    checkout scm

                    if (params.DIFF_ID) {
                        sh "arc patch ${params.DIFF_ID}"
                    }

                    dockerImage = docker.build("${DOCKERHUB_SERVER}/${PROJECT_NAME}:${APPLICATION_VERSION}", ". --target release");
                }
            }
        }

        stage('Test') {
            steps {
                // Run unit tests
                script {
                    docker.withRegistry("https://${DOCKERHUB_SERVER}", 'aereusdev-dockerhub') {
                        sh 'docker-compose -f docker/docker-compose-test.yml build'
                        sh 'docker-compose -f docker/docker-compose-test.yml up --exit-code-from netric_server'
                    }

                    // Report on junit
                    junit '.reports/junit.xml'

                    // Send reports to server for code quality metrics
//                     codeQualityReport(
//                        repositoryName: 'netric.svc',
//                        teamName: 'Netric',
//                        cloverFile: '.reports/clover.xml',
//                        pmdFile: '.reports/pmd.xml',
//                        checkStyleFile: '.reports/checkstyle.xml'
//                     )
                }
            }
        }

        stage('Publish') {
            when {
                // Do not publish
                expression {
                    return !params.DIFF_ID
                }
            }
            steps {
                script {
                    docker.withRegistry('https://dockerhub.aereus.com', 'aereusdev-dockerhub') {
                        dockerImage.push()
                    }
                }
            }
        }

        // TODO: We are not using the integration enviornment for now
        // stage('Integration Setup') {
        //     steps {
        //         // Call stack deploy to upgrade
        //         script {
        //             sshagent (credentials: ['aereus']) {
        //                 withCredentials([[$class: 'UsernamePasswordMultiBinding', credentialsId: 'aereusdev-dockerhub',
        //                                     usernameVariable: 'USERNAME', passwordVariable: 'PASSWORD']]) {
        //                     sh "ssh -o StrictHostKeyChecking=no aereus@dev1.aereus.com " +
        //                         "docker login -u ${USERNAME} -p ${PASSWORD} dockerhub.aereus.com"
        //                     sh "ssh -o StrictHostKeyChecking=no aereus@dat1-int-locsea.aereus.com docker run -i --rm -e 'APPLICATION_ENV=integration' -e 'APPLICATION_VER=${APPLICATION_VERSION}' " +
        //                         "-v /var/aereusdata/secrets/netric:/var/run/secrets:ro " +
        //                         "--entrypoint='/netric-setup.sh' dockerhub.aereus.com/${PROJECT_NAME}:${APPLICATION_VERSION}"
        //                 }
        //             }
        //         }
        //     }
        // }

        // stage('Integration') {
        //     steps {
        //         script {
        //             deployToSwarm(
        //                 environment: DeploymentTargets.INTEGRATION,
        //                 stackName: PROJECT_NAME,
        //                 imageTag: APPLICATION_VERSION,
        //                 serviceDomain: '*.integ.netric.com'
        //             )
        //         }
        //     }
        // }

        // Update is now handled as part of the deploy, and longer-running jobs need to
        // be put into the daemon - TODO
        // stage('Production Setup') {
        //     when {
        //         // Do not publish
        //         expression {
        //             return !params.DIFF_ID
        //         }
        //     }
        //     steps {
        //         withCredentials([[$class: 'UsernamePasswordMultiBinding', credentialsId: 'aereusdev-dockerhub',
        //             usernameVariable: 'USERNAME', passwordVariable: 'PASSWORD']]) {
        //             // SSH into the server
        //             sshagent (credentials: ['aereus']) {
        //                 sh "ssh -o StrictHostKeyChecking=no aereus@web2.aereus.com " +
        //                 "docker login -u ${USERNAME} -p ${PASSWORD} dockerhub.aereus.com"
        //                 sh "ssh -o StrictHostKeyChecking=no aereus@web2.aereus.com " +
        //                 "docker run -i --rm -e 'APPLICATION_ENV=production' -e 'APPLICATION_VER=${APPLICATION_VERSION}' " +
        //                 "-v /var/aereusdata/secrets/netric:/var/run/secrets:ro " +
        //                 "--network=service_netric " +
        //                 "--entrypoint='/netric-update.sh' dockerhub.aereus.com/${PROJECT_NAME}:${APPLICATION_VERSION}"
        //             }
        //         }
        //     }
        // }

        stage('Production') {
            agent { node { label 'deploy' } }
            when {
                // Do not publish
                expression {
                    return !params.DIFF_ID
                }
            }
            steps {
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
