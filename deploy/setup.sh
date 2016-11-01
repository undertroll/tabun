#!/bin/bash

set -e

ssh-keyscan -p ${deploy_port} -H ${deploy_host} 2>&1 | tee -a $HOME/.ssh/known_hosts
openssl aes-256-cbc -K ${encrypted_6a519e1377ad_key} -iv ${encrypted_6a519e1377ad_iv} -in deploy/deploy_rsa.enc -out /tmp/deploy_rsa -d
eval "$(ssh-agent -s)"
chmod 600 /tmp/deploy_rsa


cat <<END | tee -a  $HOME/.ssh/config
Host work
    Hostname ${deploy_host}
    IdentityFile /tmp/deploy_rsa
    User ${deploy_user}
    Port ${deploy_port}
    TCPKeepAlive yes
    ServerAliveInterval 5
    ControlMaster auto
    ControlPath /tmp/%r@%h:%p
END
