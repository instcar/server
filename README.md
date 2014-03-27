InstCar server side
======

1.环境安装
----------
### GIT(http://git-scm.com/)

如果你恰好在使用ubuntu/debian系统，使用`sudo apt-get install git`安装即可；如果你使用的是fedora/centos系统，使用`sudo yum install git`安装即可。

如果你一不小心是百度的，而且希望使用开发机（百度开发机系统很老而且是redhat）开发，则推荐安装jumbo工具（此工具可以到 http://babel.baidu.com 上搜索安装），然后使用`jumbo install git`安装即可，简单方便，实在是居家旅行必备良药。

慢着，我们还有别的选择，那就是github官方提供的windows和mac的GUI工具：

- [Github for Windows](http://windows.github.com/)
- [Github for Mac](http://mac.github.com/)

如果以上都没有cover到你的系统，那么请下载git源码包编译安装，忘了我吧，祝你好运！

### 基础库依赖

sudo apt-get install libssl-dev

sudo apt-get install libxml2-dev

sudo apt-get install bison

sudo apt-get install g++

sudo apt-get install libjpeg8-dev

sudo apt-get install libpng12-dev

sudo apt-get install libcurl4-nss-dev

sudo apt-get install libncurses5-dev

sudo apt-get install libmcrypt-dev

### [PHP 5.3.28](http://cn2.php.net/downloads.php)

这次我们的后端业务完全采用PHP开发，这里不争论哪种语言好，只说哪种语言我们最善长。

由于PHP扩展丰富，这里我只给出编译选项，

      ./configure --prefix=/path/you/want/to/install/php --with-apxs2=/path/to/httpd/bin/apxs --enable-mysqlnd --with-mysql=mysqlnd --with-mysqli=mysqlnd --with-pdo-mysql=mysqlnd --enable-pcntl --with-mcrypt --with-libxml-dir --with-gd --with-jpeg-dir --with-png-dir --with-curl --with-openssl
      make
      make install

### apache / nginx

以下编译安装以apache为例

#### [apr 1.5.X](http://apr.apache.org/download.cgi)：

./configure --prefix=/home/work/local/apr --enable-shared --enable-threads
make && make install

#### [apr-util 1.5.X](http://apr.apache.org/download.cgi)：

./configure --prefix=/home/work/local/apr-util --with-apr=/home/work/local/apr/
make && make install

#### [httpd 2.2.X](http://httpd.apache.org/download.cgi)：

./configure --prefix=/home/work/local/httpd --enable-rewrite --enable-alias --enable-dav --with-apr=/home/work/local/apr --with-apr-util=/home/work/local/apr-util --enable-proxy
make && make install


### [MySQL 5.1.73/5.5.37](http://dev.mysql.com/downloads/mysql/)

      mkdir build
      cmake .. -DCMAKE_INSTALL_PREFIX = "/mysql"
      make
      make install DESTDIR="/path/to/mysql"


2.PHP框架
------------
### 安装

本次我们采用C-扩展级PHP框架[Phalcon 1.3.0](http://phalconphp.com)。
使用`git clone git@github.com:phalcon/cphalcon.git`下载源代码，然后切换到分支1.3.0 `git checkout 1.3.0`。
接下来编译安装即可。

       cd build
       sh install

当然如果你没有为PHP配置PATH路径，可能需要按照PHP扩展标准安装方法安装。也即：

       /path/to/phpize
      ./configure --with-php-config=/path/to/php-config
      make
      make install

如果是Windows系统则可以在官方网站下载dll进行安装，具体见：http://phalconphp.com/en/download 。

### 配置

很简单，要让phalcon生效只需要在php.ini中加入这一行即可。

      extension=phalcon.so

当然，为了后面的业务框架能正常运行，我们还需要加入以下两行：

      phalcon.env=dev
      phalcon.conf_type=php

上面两行的意思是告诉业务框架，运行环境是dev,配置文件类型是标准PHP文件。业务框架会根据这个配置寻找`dev.php`这个配置文件。其中，`phalcon.env`建议大家配成不一样的，而`phalcon.conf_type`只接受php和ini两个值。


3.业务框架及其应用
---------------

      git clone git@github.com:BullSoft/falcon.git
      cd falcon
      git clone https://github.com/instcar/server
