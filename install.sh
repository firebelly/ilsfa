#!/usr/bin/env bash

echo "Installing Composer requirements..."
composer install

echo "Installing Bundler and all gems for Capistrano deploy..."
gem install bundler
bundle install

echo "Copying .env.example to .env..."
cp .env.example .env
echo "Copying .env.staging.example to .env.staging..."
cp .env.staging.example .env.staging

# Is this an initial fb-bedrock/sage install? (i.e. development hasn't started)
if [ -f "web/app/themes/fb-sage" ]
then

  echo "\n\nRunning initial fb-bedrock/sage install..."
  # Make sure fb-sage is up-to-date
  git submodule foreach git pull origin master

  cd web/app/themes/fb-sage
  npm install bower
  npm install
  bower install
  npx gulp

  # Theme name sent along with command?
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


else

  # This is an install for a fresh pull from a repo of a site already in progress
  echo "\n\nRunning install for freshly pulled repo of a site in progress..."

  # Pull theme name (assuming only a single theme!)
  theme=`ls web/app/themes/`

  printf "Updating .env with %s.localhost, %s_dev, etc...\n" $theme $theme
  sed -i "" "s/example.com/${theme}.localhost/g" .env.example
  sed -i "" "s/fb-sage.localhost/${theme}.localhost/g" .env.example
  sed -i "" "s/database_name/${theme}_dev/g" .env.example
  sed -i "" "s/database_user/root/g" .env.example
  sed -i "" "s/database_password//g" .env.example

  read -p "Create ${theme}_dev database? (y/n) :" -n 1 -r
  echo
  if [[ $REPLY =~ ^[Yy]$ ]]
  then
      echo "Running: mysql -u root -p -e \"create database ${theme}_dev\""
      mysql -u root -p -e "create database ${theme}_dev2";
  fi

fi
