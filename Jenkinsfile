node {
    def dockerImage;
    def clientImage;
    def nodeIp = InetAddress.localHost.hostAddress
    currentBuild.result = "SUCCESS"

    try {
        stage('Build') {
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

            dir('.clair') {
                git branch: 'master',
                    credentialsId: '9862b4cf-a692-43c5-9614-9d93114f93a7',
                    url: 'ssh://git@src.aereusdev.com/source/clair.aereusdev.com.git'
            }
            sh '.clair/clair.aereusdev.com/bin/clair-scanner_linux_amd64 -c http://192.168.1.25:6060 --ip=${nodeIp} netric'
        }

        stage('Test') {
            sh 'docker-compose -f docker/docker-compose-test.yml pull'
            sh 'docker-compose -f docker/docker-compose-test.yml build'
            sh 'docker-compose -f docker/docker-compose-test.yml up -d'
            // Give mogilefs time to settle
            sleep 30
            sh 'docker exec docker_netric_server_1 /netric-setup.sh'
            sh 'docker exec docker_netric_server_1 /netric-tests.sh'
            sh 'docker-compose -f docker/docker-compose-test.yml down'
            junit 'tests/tmp/logfile.xml'
        }

        stage('Security Scan') {
            dir('.clair') {
                git branch: 'master',
                    credentialsId: '9862b4cf-a692-43c5-9614-9d93114f93a7',
                    url: 'ssh://git@src.aereusdev.com/source/clair.aereusdev.com.git'
            }
            sh '.clair/clair.aereusdev.com/bin/clair-scanner_linux_amd64 -c http://192.168.1.25:6060 --ip=${nodeIp} netric'
        }

        stage('Publish') {
            docker.withRegistry('https://dockerhub.aereusdev.com', 'aereusdev-dockerhub') {
                /* If this is the master branch, publish to stable, if it is develop publish to latest */
                if (env.BRANCH_NAME == 'master') {
                    dockerImage.push("stable")
                } else {
                    dockerImage.push("latest")
                }
            }
        }

        stage('Integration') {
            sshagent (credentials: ['aereus']) {
                sh 'scp -P 222 -o StrictHostKeyChecking=no scripts/pull-and-run-setup.sh aereus@dev1.aereusdev.com:/home/aereus/pull-and-run-setup.sh'
                sh 'ssh -p 222 -o StrictHostKeyChecking=no aereus@dev1.aereusdev.com chmod +x /home/aereus/pull-and-run-setup.sh'
                sh 'ssh -p 222 -o StrictHostKeyChecking=no aereus@dev1.aereusdev.com /home/aereus/pull-and-run-setup.sh integration'
                sh 'ssh -p 222 -o StrictHostKeyChecking=no aereus@dev1.aereusdev.com rm /home/aereus/pull-and-run-setup.sh'
            }
        }

        stage('Production') {
            sshagent (credentials: ['aereus']) {
                // Run Setup First
                sh 'scp -o StrictHostKeyChecking=no scripts/pull-and-run-setup.sh aereus@db2.aereus.com:/home/aereus/pull-and-run-setup.sh'
                sh 'ssh -o StrictHostKeyChecking=no aereus@db2.aereus.com chmod +x /home/aereus/pull-and-run-setup.sh'
                sh 'ssh -o StrictHostKeyChecking=no aereus@db2.aereus.com /home/aereus/pull-and-run-setup.sh production'
                sh 'ssh -o StrictHostKeyChecking=no aereus@db2.aereus.com rm /home/aereus/pull-and-run-setup.sh'

                // Now Run the Daemon
                sh 'scp -o StrictHostKeyChecking=no scripts/pull-and-run-daemon.sh aereus@db2.aereus.com:/home/aereus/pull-and-run-daemon.sh'
                sh 'ssh -o StrictHostKeyChecking=no aereus@db2.aereus.com chmod +x /home/aereus/pull-and-run-daemon.sh'
                sh 'ssh -o StrictHostKeyChecking=no aereus@db2.aereus.com /home/aereus/pull-and-run-daemon.sh latest'
                sh 'ssh -o StrictHostKeyChecking=no aereus@db2.aereus.com rm /home/aereus/pull-and-run-daemon.sh'
            }
        }

        stage('Cleanup') {
            deleteDir()
            sh 'docker system prune -f'
        }

    } catch (err) {
        //sh 'docker system prune -f'

        currentBuild.result = "FAILURE"
        mail body: "project build error is here: ${env.BUILD_URL}" ,
        subject: 'project build failed',
        to: 'sky.stebnicki@aereus.com'
        throw err
    }
}
