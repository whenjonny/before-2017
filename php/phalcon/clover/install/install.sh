cd ..
cd public
## todo ln -s /data/images images
sudo chmod 777 images -R

cd ..
cd apps
sudo mkdir tmp
sudo mkdir tmp/cache
sudo mkdir tmp/logs
sudo mkdir tmp/logs/beanstalkd
sudo mkdir tmp/logs/sql
sudo mkdir tmp/logs/sys
sudo mkdir tmp/logs/debug

sudo chmod 777 tmp/ -R
