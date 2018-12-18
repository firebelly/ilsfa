#!/usr/bin/env bash

# make sure fb-sage is up-to-date
git submodule foreach git pull origin master

composer install
gem install bundler
bundle install
cp .env.example .env
cp .env.staging.example .env.staging

cd web/app/themes/fb-sage
npm install bower
npm install
bower install
npx gulp

# theme name sent along with command?
if [ $# -eq 1 ]
  then
    cd ../../../../

    echo "Renaming fb-sage to $1..."
    mv web/app/themes/fb-sage web/app/themes/$1

    echo "Updating .env with $1.localhost, $1_dev, etc..."
    sed -i "" "s/example.com/$1.localhost/g" .env
    sed -i "" "s/fb-sage.localhost/$1.localhost/g" .env
    sed -i "" "s/database_name/$1_dev/g" .env

    echo "Updating deploy.rb with theme, domain, etc..."
    sed -i "" "s/fb-sage/$1/g" config/deploy.rb
    sed -i "" "s/fb-bedrock/$1/g" config/deploy.rb

    echo "Updating manifest.json with $1.localhost..."
    sed -i "" "s/fb-sage/$1/g" web/app/themes/$1/assets/manifest.json

    read -p "Create $1_dev database? (y/n) :" -n 1 -r
    echo
    if [[ $REPLY =~ ^[Yy]$ ]]
    then
        echo "Running: mysql -u root -p -e \"create database $1_dev\""
        mysql -u root -p -e "create database $1_dev";
    fi

    read -p "Clear out .git dirs and start new repo? (y/n) :" -n 1 -r
    echo
    if [[ $REPLY =~ ^[Yy]$ ]]
    then
        echo "Removing .git dirs..."
        rm -rf .git web/app/themes/$1/.git
        git init && git add . && git commit -am "Initial repo"
        echo "Now create $1 repo on GitHub and run: git remote add origin git@github.com:firebelly/$1.git && git push -u origin master"
    fi
fi
