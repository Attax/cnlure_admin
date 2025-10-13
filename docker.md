# Docker学习笔记

标签（空格分隔）： docker

---

## Docker核心概念

### 镜像

镜像是一个只读的模板，包含了运行应用所需的所有内容：代码、运行时、库文件、环境变量和配置文件，可以用来创建 Docker 容器，一个镜像可以创建很多容器。

Docker 镜像 是一个特殊的文件系统，除了提供容器运行时所需的程序、库、资源、配置等文件外，还包含了一些为运行时准备的一些配置参数（如匿名卷、环境变量、用户等）。镜像 不包含 任何动态数据，其内容在构建之后也不会被改变。

#### 分层存储
因为镜像包含操作系统完整的 root 文件系统，其体积往往是庞大的，因此在 Docker 设计时，就充分利用 Union FS 的技术，将其设计为分层存储的架构。所以严格来说，镜像并非是像一个 ISO 那样的打包文件，镜像只是一个虚拟的概念，其实际体现并非由一个文件组成，而是由一组文件系统组成，或者说，由多层文件系统联合组成。

镜像构建时，会一层层构建，前一层是后一层的基础。每一层构建完就不会再发生改变，后一层上的任何改变只发生在自己这一层。比如，删除前一层文件的操作，实际不是真的删除前一层的文件，而是仅在当前层标记为该文件已删除。在最终容器运行的时候，虽然不会看到这个文件，但是实际上该文件会一直跟随镜像。因此，在构建镜像的时候，需要额外小心，每一层尽量只包含该层需要添加的东西，任何额外的东西应该在该层构建结束前清理掉。

分层存储的特征还使得镜像的复用、定制变的更为容易。甚至可以用之前构建好的镜像作为基础层，然后进一步添加新的层，以定制自己所需的内容，构建新的镜像。


### 容器

容器是从镜像创建的运行实例，是一个轻量级、可移植的执行环境，它可以被启动、开始、停止、删除，是独立运行的一个或一组应用。

镜像（Image）和容器（Container）的关系，就像是面向对象程序设计中的 类 和 实例 一样，镜像是静态的定义，容器是镜像运行时的实体。容器可以被创建、启动、停止、删除、暂停等。

容器的实质是进程，但与直接在宿主执行的进程不同，容器进程运行于属于自己的独立的 命名空间。因此容器可以拥有自己的 root 文件系统、自己的网络配置、自己的进程空间，甚至自己的用户 ID 空间。容器内的进程是运行在一个隔离的环境里，使用起来，就好像是在一个独立于宿主的系统下操作一样。这种特性使得容器封装的应用比直接在宿主运行更加安全。也因为这种隔离的特性，很多人初学 Docker 时常常会混淆容器和虚拟机。

每一个容器运行时，是以镜像为基础层，在其上创建一个当前容器的存储层，我们可以称这个为容器运行时读写而准备的存储层为 容器存储层。

容器存储层的生存周期和容器一样，容器消亡时，容器存储层也随之消亡。因此，任何保存于容器存储层的信息都会随容器删除而丢失。

按照 Docker 最佳实践的要求，容器不应该向其存储层内写入任何数据，容器存储层要保持无状态化。所有的文件写入操作，都应该使用 数据卷（Volume）、或者 绑定宿主目录，在这些位置的读写会跳过容器存储层，直接对宿主（或网络存储）发生读写，其性能和稳定性更高。

数据卷的生存周期独立于容器，容器消亡，数据卷不会消亡。因此，使用数据卷后，容器删除或者重新运行之后，数据却不会丢失。

### 仓库

仓库是集中存放镜像的地方，分为公共仓库和私有仓库。

公共仓库：Docker Hub 是一个公共的镜像仓库，用户可以在 Docker Hub 上搜索、下载和分享镜像。

私有仓库：用户可以搭建自己的私有仓库，用于存储和分享自己的镜像。


镜像构建完成后，可以很容易的在当前宿主机上运行，但是，如果需要在其它服务器上使用这个镜像，我们就需要一个集中的存储、分发镜像的服务，Docker Registry 就是这样的服务。

一个 Docker Registry 中可以包含多个 仓库（Repository）；每个仓库可以包含多个 标签（Tag）；每个标签对应一个镜像。

通常，一个仓库会包含同一个软件不同版本的镜像，而标签就常用于对应该软件的各个版本。我们可以通过 <仓库名>:<标签> 的格式来指定具体是这个软件哪个版本的镜像。如果不给出标签，将以 latest 作为默认标签。

以 Ubuntu 镜像 为例，ubuntu 是仓库的名字，其内包含有不同的版本标签，如，16.04, 18.04。我们可以通过 ubuntu:16.04，或者 ubuntu:18.04 来具体指定所需哪个版本的镜像。如果忽略了标签，比如 ubuntu，那将视为 ubuntu:latest。

仓库名经常以 两段式路径 形式出现，比如 jwilder/nginx-proxy，前者往往意味着 Docker Registry 多用户环境下的用户名，后者则往往是对应的软件名。但这并非绝对，取决于所使用的具体 Docker Registry 的软件或服务。


#### Docker Registry 公开服务
Docker Registry 公开服务是开放给用户使用、允许用户管理镜像的 Registry 服务。一般这类公开服务允许用户免费上传、下载公开的镜像，并可能提供收费服务供用户管理私有镜像。

#### 私有 Docker Registry

除了使用公开服务外，用户还可以在本地搭建私有 Docker Registry。Docker 官方提供了 Docker Registry 镜像，可以直接使用做为私有 Registry 服务。

---


## Docker Destktop 安装

### 配置网络代理

docker desktop需要登录，由于众所周知的原因，需要配置网络代理才能登录

![docker配置网络代理进行登录验证][1]


[1]: https://i-blog.csdnimg.cn/blog_migrate/f0e5d68f66024066a5de9501e950207f.png
  

### 配置镜像加速
同样的，也需要配置docker registry镜像为国内的源

在设置页的Docker Engine 选项里添加配置

```json
{
    "registry-mirrors": [
        "https://mirror.ccs.tencentyun.com"
    ]
}
```

或者直接修改docker配置文件
```bash
vim /etc/docker/daemon.json
```

```json
{
    "registry-mirrors": [
        "https://mirror.ccs.tencentyun.com"
    ]
}
```

运行docker info 命令，查看docker配置

```bash
docker info
```

常用命令：
```bash
docker run - 运行容器
docker build - 构建镜像
docker pull - 拉取镜像
docker ps - 查看容器状态
docker exec - 在容器中执行命令
docker stop - 停止容器
docker start - 启动容器
docker restart - 重启容器
docker rm - 删除容器
docker rmi - 删除镜像
```

---


## Docker Hello World

Docker 允许你在容器内运行应用程序， 使用 `docker run` 命令来在容器内运行一个应用程序

### Hello World
```bash
docker run ubuntu /bin/echo "Hello world"
```

docker: Docker 的二进制执行文件。

run: 与前面的 docker 组合来运行一个容器。

ubuntu: 指定要运行的镜像，如果未指定版本号，默认使用最新版，Docker 首先从本地主机上查找镜像是否存在，如果不存在，Docker 就会从镜像仓库 Docker Hub 下载公共镜像。

echo "Hello world": 在启动的容器里执行的命令


### 运行交互式的容器

我们通过 docker 的两个参数 -i -t，让 docker 运行的容器实现"对话"的能力：
```bash
docker run -it ubuntu /bin/bash
```

各个参数解析：

-t: 在新容器内指定一个伪终端或终端。

-i: 允许你对容器内的标准输入 (STDIN) 进行交互

可以通过运行 exit 命令或者使用 CTRL+D 来退出容器

### 启动容器（后台模式）

我们可以使用 -d 参数来指定容器在后台运行

```bash
docker run -itd ubuntu /bin/bash
```

运行命令后会输出类似“329b056a5460c97b8778cf1b90a595b628b5fcb2673a405e35194e889c15979e”这种字符串，这是容器的ID,对每个容器来说都是唯一的，我们可以通过容器 ID 来查看对应的容器发生了什么

可以通过运行`docker ps` 命令来查看容器的运行状态

```bash
docker ps
```

会输出类似如下的结果：

```bash
CONTAINER ID   IMAGE     COMMAND                   CREATED              STATUS              PORTS                  NAMES
329b056a5460   ubuntu    "/bin/bash"               About a minute ago   Up About a minute                          sharp_haslett
fa692313aa76   ubuntu    "/bin/bash"               About a minute ago   Up About a minute                          ecstatic_diffie
18dd4bd878c2   nginx     "/docker-entrypoint.…"   14 minutes ago       Up 14 minutes       0.0.0.0:8080->80/tcp   gallant_cohen
```
输出详情介绍：

CONTAINER ID: 容器 ID。

IMAGE: 使用的镜像。

COMMAND: 启动容器时运行的命令。

CREATED: 容器的创建时间。

STATUS: 容器状态。

PORTS: 容器的端口信息和使用的连接类型（tcp\udp）。

NAMES: 自动分配的容器名称。

在宿主主机内使用 docker logs 命令，查看容器内的标准输出

---

## docker 容器

### 镜像与容器的关系

镜像（Image）：容器的静态模板，包含了应用程序运行所需的所有依赖和文件。镜像是不可变的。

容器（Container）：镜像的一个运行实例，具有自己的文件系统、进程、网络等，且是动态的。容器从镜像启动，并在运行时保持可变。


###  运行docker container

运行`docker run ` 运行指定版本镜像

```bash
docker run nginx
```

默认运行最新镜像，等同于

```bash
docker run ubuntu:latest
```

### 进入容器


在使用 -d 参数时启动容器时，容器会运行在后台，这时如果要进入容器，可以通过以下命令进入：

docker attach：允许你与容器的标准输入（stdin）、输出（stdout）和标准错误（stderr）进行交互。

docker exec：推荐大家使用 docker exec 命令，因为此命令会退出容器终端，但不会导致容器的停止。



#### attach 命令

使用 docker attach 命令实例：

```bash
docker attach acce09308e68 
```


注意： 如果从这个容器退出，会导致容器的停止。

#### exec 命令

使用 docker exec 命令实例：

```bash
docker exec -it acce09308e68 /bin/bash
```

注意： 如果从这个容器退出，容器不会停止，这就是为什么推荐大家使用 docker exec。


### 查看容器信息

运行`docker inspect containerName` 查看容器信息

```bash
docker inspect nginx
```

### 查看运行中的容器

运行`docker ps` 查看运行中的容器

```bash
docker ps
```

### 查看所有容器

运行`docker ps -a` 查看所有容器

```bash
docker ps -a
```

### 停止容器

运行`docker stop` 停止指定容器

```bash
docker stop nginx
```

### 停止所有容器

运行`docker stop $(docker ps -a -q)` 停止所有容器

```bash
docker stop $(docker ps -a -q)
```

### 重启容器

运行`docker restart containerId` 重启指定容器

```bash
docker restart nginx
```

### 启动已停止容器

运行`docker start containerName` 启动指定容器

```bash
docker start nginx
```



### 删除容器

运行`docker rm containerName` 删除指定容器

```bash
docker rm ubuntu
```

### 查看容器资源使用情况

运行`docker stats containerName` 查看指定容器资源使用情况

```bash
docker stats nginx
```


### 导入导出容器

运行`docker export containerName` 导出容器

```bash
docker export nginx > nginx.tar
```

运行`docker import nginx.tar nginx:latest` 导入容器

```bash
docker import nginx.tar nginx:latest
```
### 删除容器

运行`docker rm containerName` 删除指定容器

```bash
docker rm nginx
```


### 运行一个 web 应用

运行`docker run -d -P softwareName[:tag]` 运行指定版本镜像

```bash
docker run -d -P nginx:latest
```

默认运行最新镜像，等同于

```bash
docker run -d -P nginx:latest
```

参数说明:

-d:让容器在后台运行。

-P:将容器内部使用的网络端口随机映射到我们使用的主机上


也可以通过 -p 参数来设置不一样的端口：

```bash
docker run -d -p 8080:80 nginx:latest
```

---


## docker镜像使用

当运行容器时，使用的镜像如果在本地中不存在，docker 就会自动从 docker 镜像仓库中下载，默认是从 Docker Hub 公共镜像源下载。

### 查看本地镜像

运行`docker images` 查看本地镜像

```bash
docker images
```

输出类型下面的内容

```bash
REPOSITORY   TAG       IMAGE ID       CREATED       SIZE
ubuntu       latest    802541663949   3 weeks ago   78.1MB
nginx        latest    41f689c20910   4 weeks ago   192MB
```

各个选项说明:

REPOSITORY：表示镜像的仓库源

TAG：镜像的标签

IMAGE ID：镜像ID

CREATED：镜像创建时间

SIZE：镜像大小

### 查找镜像

```bash
docker search softwareName
```

如果是国内网络，可能会超时（配置了镜像源对docker search命令没有效果）


### 拉取第一个镜像

运行`docker pull softwareName[:tag]` 拉取指定版本镜像

```bash
docker pull ubuntu
```

默认拉取最新镜像，等同于

```bash
docker pull ubuntu:latest
```

### 删除镜像

运行`docker rmi imageName[:tag]` 删除指定版本镜像

```bash
docker rmi ubuntu
```

默认删除最新镜像，等同于

```bash
docker rmi ubuntu:latest
```


### 创建镜像

当我们从 docker 镜像仓库中下载的镜像不能满足我们的需求时，我们可以通过以下两种方式对镜像进行更改。

1、从已经创建的容器中更新镜像，并且提交这个镜像
2、使用 Dockerfile 指令来创建一个新的镜像


#### 更新镜像

更新镜像前，我们需要使用镜像创建一个容器

```bash
docker run -it ubuntu:latest /bin/bash
```

进入容器后，执行我们要做的更新操作，比如安装软件、配置环境变量等。

比如安装软件

```bash
apt-get update
apt-get upgrade
apt-get install -y softwareName
apt-get install -y softwareName2
```

在完成操作后，输入exit退出容器

```bash
exit
```


在容器中对文件进行修改后，我们可以使用 `docker commit` 命令来提交容器的更改。

```bash
docker commit -m "更新软件" -a "作者" 容器ID 镜像名:版本
```

比如：
```bash
 docker commit -m 'update ubuntu' -a='attax' b9bbcc66c2e5 attax/ubuntu
 sha256:8acaada3115a70101238ffe090dafcb2d3b3b573ec83340cdfa113238b98c3a9
```

执行docker images命令查看修改后的镜像

```bash
REPOSITORY     TAG       IMAGE ID       CREATED              SIZE
attax/ubuntu   latest    8acaada3115a   About a minute ago   78.1MB
ubuntu         latest    802541663949   3 weeks ago          78.1MB
nginx          latest    41f689c20910   4 weeks ago          192MB
hello-world    latest    1b44b5a3e06a   5 weeks ago          10.1kB
alpine         latest    9234e8fb04c4   2 months ago         8.31MB
```

使用修改后的镜像

```bash
docker run -it attax/ubuntu /bin/bash
root@3ed2bfaa5454:/# 
```




#### 构建镜像

我们还可以使用命令 docker build ， 从零开始来创建一个新的镜像。我们需要创建一个 Dockerfile 文件，其中包含一组指令来告诉 Docker 如何构建镜像。

```dockerfile
FROM ubuntu:latest

MAINTAINER attax

RUN     /bin/echo 'root:123456' |chpasswd
RUN     useradd runoob
RUN     /bin/echo 'runoob:123456' |chpasswd
RUN     /bin/echo -e "LANG=\"en_US.UTF-8\"" >/etc/default/local
EXPOSE  22
EXPOSE  80
CMD     /usr/sbin/sshd -D
```
每一个指令都会在镜像上创建一个新的层，每一个指令的前缀都必须是大写的。

第一条FROM，指定使用哪个镜像源

RUN 指令告诉docker 在镜像内执行命令，安装了什么。。。

然后，我们使用 Dockerfile 文件，通过 docker build 命令来构建一个镜像。

```bash
docker build -t attax/ubuntu .
```

参数说明：

-t ：指定要创建的目标镜像名

. ：Dockerfile 文件所在目录，可以指定Dockerfile 的绝对路径

#### 设置镜像标签

```bash
docker tag 8acaada3115a attax/ubuntu
```
参数说明
docker tag 镜像ID，这里是 8acaada3115a ,用户名称、镜像源名(repository name)和新的标签名(tag)

#### 慎用 docker commit
使用 docker commit 命令虽然可以比较直观的帮助理解镜像分层存储的概念，但是实际环境中并不会这样使用。

首先，由于命令的执行，还有很多文件被改动或添加了。这还仅仅是最简单的操作，如果是安装软件包、编译构建，那会有大量的无关内容被添加进来，将会导致镜像极为臃肿。

此外，使用 docker commit 意味着所有对镜像的操作都是黑箱操作，生成的镜像也被称为 黑箱镜像，换句话说，就是除了制作镜像的人知道执行过什么命令、怎么生成的镜像，别人根本无从得知。而且，即使是这个制作镜像的人，过一段时间后也无法记清具体的操作。这种黑箱镜像的维护工作是非常痛苦的。

而且，回顾之前提及的镜像所使用的分层存储的概念，除当前层外，之前的每一层都是不会发生改变的，换句话说，任何修改的结果仅仅是在当前层进行标记、添加、修改，而不会改动上一层。如果使用 docker commit 制作镜像，以及后期修改的话，每一次修改都会让镜像更加臃肿一次，所删除的上一层的东西并不会丢失，会一直如影随形的跟着这个镜像，即使根本无法访问到。这会让镜像更加臃肿。



---

## Docker 容器连接

我们可以通过网络端口来访问运行在 docker 容器内的服务。

容器中可以运行一些网络应用，要让外部也可以访问这些应用，可以通过 -P 或 -p 参数来指定端口映射。

### 端口映射

创建一个Nginx容器

```bash
docker run -d -P nginx:latest
```

使用-P 绑定端口，使用docker ps命令可以看到容器端口绑定到主机的什么端口上

```bash
docker ps
```

```bash
CONTAINER ID   IMAGE          COMMAND                   CREATED              STATUS              PORTS                   NAMES
d5509d337417   nginx:latest   "/docker-entrypoint.…"   About a minute ago   Up About a minute   0.0.0.0:55000->80/tcp   sharp_feynman
```
可以看到容器80端口映射到主机的55000端口



快速查看容器端口映射

```bash
docker port 容器ID
```

参数说明：

容器ID：容器ID

查看容器端口映射

运行命令：

```bash
docker port d5509d337417
```

输出：

```bash
80/tcp -> 0.0.0.0:55000
```

也可以使用 -p 标识来指定容器端口绑定到主机端口。

两种方式的区别是:

-P：是容器内部端口随机映射到主机的端口。
-p：是容器内部端口绑定到指定的主机端口。

比如：

```bash
docker run -d -p 8080:80 nginx:latest
be138a5e0b11b8975108675599630d72222042bf7b8c24374b982f8b03f7726a
```

可以看到容器80端口映射到主机的8080端口

```bash
docker ps
```

```bash
CONTAINER ID   IMAGE          COMMAND                   CREATED         STATUS         PORTS                   NAMES
be138a5e0b11   nginx:latest   "/docker-entrypoint.…"   6 seconds ago   Up 5 seconds   0.0.0.0:8080->80/tcp    vigilant_feistel
d5509d337417   nginx:latest   "/docker-entrypoint.…"   4 minutes ago   Up 4 minutes   0.0.0.0:55000->80/tcp   sharp_feynman
```

我们也可以指定容器绑定的网络地址，比如绑定 127.0.0.1。

```bash
docker run -d -p 127.0.0.1:8081:80 nginx:latest
10a6e2bab805d761f12fa844d3d4f6e92e7ac2b5eb814ceafb9ff19861cadeb9
```

默认都是绑定 tcp 端口，如果要绑定 UDP 端口，可以在端口后面加上 /udp


### Docker 容器互联

端口映射并不是唯一把 docker 连接到另一个容器的方法。

docker 有一个连接系统允许将多个容器连接在一起，共享连接信息。

docker 连接会创建一个父子关系，其中父容器可以看到子容器的信息。

#### 容器命名

当我们创建一个容器的时候，docker 会自动对它进行命名。另外，我们也可以使用 --name 标识来命名容器，例如：

```bash
docker run -d --name myNginx nginx:latest      
2b4e5ebf3388fcfc4f8c12434648271fc9440488d858a4d4f8b48d87c31d6160
```

可以看到容器的名称是 nginx



使用 docker ps 命令来查看容器名称

```bash
CONTAINER ID   IMAGE          COMMAND                   CREATED          STATUS          PORTS                    NAMES
2b4e5ebf3388   nginx:latest   "/docker-entrypoint.…"   18 seconds ago   Up 18 seconds   80/tcp                   myNginx
10a6e2bab805   nginx:latest   "/docker-entrypoint.…"   8 minutes ago    Up 8 minutes    127.0.0.1:8081->80/tcp   funny_mestorf
be138a5e0b11   nginx:latest   "/docker-entrypoint.…"   10 minutes ago   Up 10 minutes   0.0.0.0:8080->80/tcp     vigilant_feistel
d5509d337417   nginx:latest   "/docker-entrypoint.…"   15 minutes ago   Up 15 minutes   0.0.0.0:55000->80/tcp    sharp_feynman
```

或者直接使用docker ps --filter name=myNginx 查看

```bash
docker ps --filter name=myNginx
```

```bash
CONTAINER ID   IMAGE          COMMAND                   CREATED              STATUS              PORTS     NAMES
2b4e5ebf3388   nginx:latest   "/docker-entrypoint.…"   About a minute ago   Up About a minute   80/tcp    myNginx
```


#### 新建网络

我们可以使用 docker network create 命令来新建一个网络，例如：

```bash
docker network create myNetwork
```

参数说明：

-d：参数指定 Docker 网络类型，有 bridge、overlay。

其中 overlay 网络类型用于 Swarm mode

可以使用 docker network ls 命令来查看网络列表

```bash
docker network ls
```

```bash
NETWORK ID     NAME       DRIVER    SCOPE
6b9cc6ff4c33   bridge     bridge    local
7dabf4be8b80   host       host      local
ecf2f8605640   none       null      local
97fe37048dec   test-net   bridge    local
```

#### 连接容器
运行一个容器并连接到新建的 test-net 网络:

```bash
 docker run -itd --name testNetwork --network test-net ubuntu /bin/bash
bacef0eab46dbf2bf8c9fc46ab484d3725cd7d596e611b03a001aacb5b2519ea
```

可以看到容器的名称是 testNetwork

```bash
CONTAINER ID   IMAGE          COMMAND                   CREATED          STATUS          PORTS                    NAMES
bacef0eab46d   ubuntu         "/bin/bash"               34 seconds ago   Up 33 seconds                            testNetwork
2b4e5ebf3388   nginx:latest   "/docker-entrypoint.…"   2 hours ago      Up 2 hours      80/tcp                   myNginx
10a6e2bab805   nginx:latest   "/docker-entrypoint.…"   2 hours ago      Up 2 hours      127.0.0.1:8081->80/tcp   funny_mestorf
be138a5e0b11   nginx:latest   "/docker-entrypoint.…"   3 hours ago      Up 3 hours      0.0.0.0:8080->80/tcp     vigilant_feistel
d5509d337417   nginx:latest   "/docker-entrypoint.…"   3 hours ago      Up 3 hours      0.0.0.0:55000->80/tcp    sharp_feynman
```

打开新的终端，再运行一个容器并加入到 test-net 网络:
```bash
docker run -itd --name testNetwork2 --network test-net ubuntu /bin/bash 
```


可以通过 ping 来证明 testNetwork 容器和 testNetwork2 容器建立了互联关系。

如果 testNetwork、testNetwork2 容器内中无 ping 命令，则在容器内执行以下命令安装 ping。

```bash
apt-get update
apt install iputils-ping
```

如果安装失败，请检查docker proxy配置是否正确。

进入testNetwork容器

```bash
docker exec -it testNetwork /bin/bash
```

执行 ping 命令测试

```bash
ping testNetwork2
```


```bash
ping testNetwork2
PING testNetwork2 (172.18.0.3) 56(84) bytes of data.
64 bytes from testNetwork2.test-net (172.18.0.3): icmp_seq=1 ttl=64 time=0.301 ms
64 bytes from testNetwork2.test-net (172.18.0.3): icmp_seq=2 ttl=64 time=0.068 ms

```



可以使用 docker network connect 命令来将容器连接到网络，例如：

```bash
docker network connect test-net test1
```

参数说明：

test-net：网络名称

test1：容器名称

可以使用 docker network inspect 命令来查看网络信息，例如：

```bash
docker network inspect test-net
```

```bash
[
    {
        "Name": "test-net",
        "Id": "97fe37048dec80e18a3ec1ac2d351ec40db9279b39fdc73ab2b55ac5cc29d4dc",
        "Created": "2025-09-15T07:59:00.082454368Z",
        "Scope": "local",
        "Driver": "bridge",
        "EnableIPv6": false,
        "IPAM": {
            "Driver": "default",
            "Options": {},
            "Config": [
                {
                    "Subnet": "172.18.0.0/16",
                    "Gateway": "172.18.0.1"
                }
            ]
        },
        "Internal": false,
        "Attachable": false,
        "Ingress": false,
        "ConfigFrom": {
            "Network": ""
        },
        "ConfigOnly": false,
        "Containers": {
            "6d2993f8d8723814696a615c82f86d75617b4edbfbd776cf1c741ec3d3167620": {
                "Name": "testNetwork2",
                "EndpointID": "ef7f34a72fbccbfdc9e99046729d429d2618e63097569d7c188800c9f732eab2",
                "MacAddress": "02:42:ac:12:00:03",
                "IPv4Address": "172.18.0.3/16",
                "IPv6Address": ""
            },
            "bacef0eab46dbf2bf8c9fc46ab484d3725cd7d596e611b03a001aacb5b2519ea": {
                "Name": "testNetwork",
                "EndpointID": "1ba25e8006e85f78cbc916696b4c724cfe974468893882e11e9d63581db6ae8f",
                "MacAddress": "02:42:ac:12:00:02",
                "IPv4Address": "172.18.0.2/16",
                "IPv6Address": ""
            }
        },
        "Options": {},
        "Labels": {}
    }
]
```

### 配置DNS

可以在宿主机的 /etc/docker/daemon.json 文件中增加以下内容来设置全部容器的 DNS：
```bash
{
  "dns" : [
    "114.114.114.114",
    "8.8.8.8"
  ]
}
```
设置后，启动容器的 DNS 会自动配置为 114.114.114.114 和 8.8.8.8。

配置完，需要重启 docker 才能生效。

查看容器的 DNS 是否生效可以使用以下命令，它会输出容器的 DNS 信息：
```bash
docker run -it --rm  ubuntu  cat etc/resolv.conf
```

#### 手动指定容器的配置

如果只想在指定的容器设置 DNS，则可以使用以下命令：
```bash
docker run -it --rm -h host_ubuntu  --dns=114.114.114.114 --dns-search=test.com ubuntu
```

参数说明：

--rm：容器退出时自动清理容器内部的文件系统。

-h HOSTNAME 或者 --hostname=HOSTNAME： 设定容器的主机名，它会被写到容器内的 /etc/hostname 和 /etc/hosts。

--dns=IP_ADDRESS： 添加 DNS 服务器到容器的 /etc/resolv.conf 中，让容器用这个服务器来解析所有不在 /etc/hosts 中的主机名。

--dns-search=DOMAIN： 设定容器的搜索域，当设定搜索域为 .example.com 时，在搜索一个名为 host 的主机时，DNS 不仅搜索 host，还会搜索 host.example.com。

如果在容器启动时没有指定 --dns 和 --dns-search，Docker 会默认用宿主主机上的 /etc/resolv.conf 来配置容器的 DNS

---

## Docker 仓库管理

```bash
docker login
docker logout
docker search
docker pull
docker push
```

---

## Dockerfile

Dockerfile 是一个文本文件，包含了构建 Docker 镜像的所有指令。

Dockerfile 是一个用来构建镜像的文本文件，文本内容包含了一条条构建镜像所需的指令和说明。


我们从之前docker commit 学习中了解到，镜像的定制实际上就是定制每一层所添加的配置、文件。如果我们可以把每一层修改、安装、构建、操作的命令都写入一个脚本，用这个脚本来构建、定制镜像，那么之前提及的无法重复的问题、镜像构建透明性的问题、体积的问题就都会解决。这个脚本就是 Dockerfile。

Dockerfile 是一个文本文件，其内包含了一条条的 指令(Instruction)，每一条指令构建一层，因此每一条指令的内容，就是描述该层应当如何构建。


### 使用 Dockerfile 定制镜像

```bash
FROM nginx
RUN echo "hello docker world" > /usr/share/nginx/html/index.html
```




#### 指令说明

FROM：定制的镜像都是基于 FROM 的镜像，这里的 nginx 就是定制需要的基础镜像。后续的操作都是基于 nginx。

RUN：用于执行后面跟着的命令行命令。有以下两种格式：

shell 格式：

```bash
RUN <命令行命令>
# <命令行命令> 等同于，在终端操作的 shell 命令。
```

exec 格式：

```bash
RUN ["可执行文件", "参数1", "参数2"]
# 例如：
# RUN ["./test.php", "dev", "offline"] 等价于 RUN ./test.php dev offline
```

注意：Dockerfile 的指令每执行一次都会在 docker 上新建一层。所以过多无意义的层，会造成镜像膨胀过大。例如：

```bash
FROM centos
RUN yum -y install wget
RUN wget -O redis.tar.gz "http://download.redis.io/releases/redis-5.0.3.tar.gz"
RUN tar -xvf redis.tar.gz
```

以上执行会创建 3 层镜像。可简化为以下格式：

```bash
FROM centos
RUN yum -y install wget \
    && wget -O redis.tar.gz "http://download.redis.io/releases/redis-5.0.3.tar.gz" \
    && tar -xvf redis.tar.gz
```
如上，以 && 符号连接命令，这样执行后，只会创建 1 层镜像。


#### FROM 指定基础镜像

所谓定制镜像，那一定是以一个镜像为基础，在其上进行定制。就像我们之前运行了一个 nginx 镜像的容器，再进行修改一样，基础镜像是必须指定的。而 FROM 就是指定 基础镜像，因此一个 Dockerfile 中 FROM 是必备的指令，并且必须是第一条指令。

除了选择现有镜像为基础镜像外，Docker 还存在一个特殊的镜像，名为 scratch。这个镜像是虚拟的概念，并不实际存在，它表示一个空白的镜像。

```bash
FROM scratch
```

如果你以 scratch 为基础镜像的话，意味着你不以任何镜像为基础，接下来所写的指令将作为镜像第一层开始存在。

不以任何系统为基础，直接将可执行文件复制进镜像的做法并不罕见，对于 Linux 下静态编译的程序来说，并不需要有操作系统提供运行时支持，所需的一切库都已经在可执行文件里了，因此直接 FROM scratch 会让镜像体积更加小巧。使用 Go 语言 开发的应用很多会使用这种方式来制作镜像，这也是有人认为 Go 是特别适合容器微服务架构的语言的原因之一。

#### RUN 执行命令
RUN 指令是用来执行命令行命令的。由于命令行的强大能力，RUN 指令在定制镜像时是最常用的指令之一。其格式有两种：


shell 格式：RUN <命令>，就像直接在命令行中输入的命令一样。刚才写的 Dockerfile 中的 RUN 指令就是这种格式。

```bash
RUN echo '<h1>Hello, Docker!</h1>' > /usr/share/nginx/html/index.html
```

exec 格式：RUN ["可执行文件", "参数1", "参数2"]，这更像是函数调用中的格式。


#### 构建镜像

使用docker build 命令构建镜像

```bash
docker build -t docker_nginx:1.0 .
```

参数说明：

-t：指定要创建的目标镜像的名称和标签。

.：Dockerfile 文件所在目录。

#### 查看打包后的镜像

使用docker images 命令查看镜像

```bash
docker images
```

参数说明：

-a：显示所有镜像（包括中间镜像层），默认只显示可见镜像。

-q：只显示镜像 ID。

#### 运行打包后的镜像

使用docker run 命令运行镜像

```bash
docker run  -d -p 8181:80 docker_nginx:1.0
```

参数说明：

-d：后台运行容器，并返回容器 ID。

-p：端口映射，格式为：主机端口:容器端口。

docker_nginx:1.0：要运行的镜像名称和标签。

访问：http://localhost:8181 查看页面会看到 hello docker world


#### 上下文路径

上下文路径，是指 docker 在构建镜像，有时候想要使用到本机的文件（比如复制），docker build 命令得知这个路径后，会将路径下的所有内容打包。

如果未说明最后一个参数，那么默认上下文路径就是 Dockerfile 所在的位置。

注意：上下文路径下不要放无用的文件，因为会一起打包发送给 docker 引擎，如果文件过多会造成过程缓慢。


---

## Dockerfile 指令

Dockerfile 指令有很多，这里只介绍常用的一些指令。

| Dockerfile | 指令 | 说明 |
| --- | --- | --- |
| FROM | 指定基础镜像，用于后续的指令构建。 |
| MAINTAINER | 指定Dockerfile的作者/维护者。（已弃用，推荐使用LABEL指令） |
| LABEL | 添加镜像的元数据，使用键值对的形式。 |
| RUN | 在构建过程中在镜像中执行命令。 |
| CMD | 指定容器创建时的默认命令。（可以被覆盖） |
| ENTRYPOINT | 设置容器创建时的主要命令。（不可被覆盖） |
| EXPOSE | 声明容器运行时监听的特定网络端口。 |
| ENV | 在容器内部设置环境变量。 |
| ADD | 将文件、目录或远程URL复制到镜像中。 |
| COPY | 将文件或目录复制到镜像中。 |
| VOLUME | 为容器创建挂载点或声明卷。 |
| WORKDIR | 设置后续指令的工作目录。 |
| USER | 指定后续指令的用户上下文。 |
| ARG | 定义在构建过程中传递给构建器的变量，可使用 "docker build" 命令设置。 |
| ONBUILD | 当该镜像被用作另一个构建过程的基础时，添加触发器。 |
| STOPSIGNAL | 设置发送给容器以退出的系统调用信号。 |
| HEALTHCHECK | 定义周期性检查容器健康状态的命令。 |
| SHELL | 覆盖Docker中默认的shell，用于RUN、CMD和ENTRYPOINT指令。 |




### FROM

FROM 指令是 Dockerfile 的第一条指令，用于指定基础镜像。

### RUN

RUN 指令用于在容器中执行命令。

### COPY

COPY 从上下文目录中复制文件或者目录到容器里指定路径。

格式：

```bash
COPY [--chown=<user>:<group>] <源路径>... <目标路径>

COPY [--chown=<user>:<group>] ["<源路径1>",... "<目标路径>"]
```

在使用该指令的时候还可以加上 --chown=<user>:<group> 选项来改变文件的所属用户及所属组。

```bash
COPY --chown=55:mygroup files* /mydir/
COPY --chown=bin files* /mydir/
COPY --chown=1 files* /mydir/
COPY --chown=10:11 files* /mydir/
```

### ADD

ADD 指令用于将文件从主机复制到容器中，与 COPY 指令不同的是，ADD 指令可以自动解压缩文件。
ADD 指令会令镜像构建缓存失效，从而可能会令镜像构建变得比较缓慢。

因此在 COPY 和 ADD 指令中选择的时候，可以遵循这样的原则，所有的文件复制均使用 COPY 指令，仅在需要自动解压缩的场合使用 ADD。

在使用该指令的时候还可以加上 --chown=<user>:<group> 选项来改变文件的所属用户及所属组。

```bash
ADD --chown=55:mygroup files* /mydir/
ADD --chown=bin files* /mydir/
ADD --chown=1 files* /mydir/
ADD --chown=10:11 files* /mydir/
```


### EXPOSE

EXPOSE 指令用于指定容器运行时监听的端口。

ENTRYPOINT 的格式和 RUN 指令格式一样，分为 exec 格式和 shell 格式。

ENTRYPOINT 的目的和 CMD 一样，都是在指定容器启动程序及参数。ENTRYPOINT 在运行时也可以替代，不过比 CMD 要略显繁琐，需要通过 docker run 的参数 --entrypoint 来指定。

当指定了 ENTRYPOINT 后，CMD 的含义就发生了改变，不再是直接的运行其命令，而是将 CMD 的内容作为参数传给 ENTRYPOINT 指令，换句话说实际执行时，将变为：

```bash
<ENTRYPOINT> "<CMD>"
```

那么有了 CMD 后，为什么还要有 ENTRYPOINT 呢？这种 <ENTRYPOINT> "<CMD>" 有什么好处么？让我们来看几个场景。

### 场景一：让镜像变成像命令一样使用

假设我们需要一个得知自己当前公网 IP 的镜像，那么可以先用 CMD 来实现：

```bash
FROM ubuntu:18.04
RUN apt-get update \
    && apt-get install -y curl \
    && rm -rf /var/lib/apt/lists/*
CMD [ "curl", "-s", "http://myip.ipip.net" ]
```

假如我们使用 docker build -t myip . 来构建镜像的话，如果我们需要查询当前公网 IP，只需要执行：

```bash
$ docker run myip
当前 IP：61.148.226.66 来自：北京市 联通
```
嗯，这么看起来好像可以直接把镜像当做命令使用了，不过命令总有参数，如果我们希望加参数呢？比如从上面的 CMD 中可以看到实质的命令是 curl，那么如果我们希望显示 HTTP 头信息，就需要加上 -i 参数。那么我们可以直接加 -i 参数给 docker run myip 么？

```bash
$ docker run myip -i
docker: Error response from daemon: invalid header field value "oci runtime error: container_linux.go:247: starting container process caused \"exec: \\\"-i\\\": executable file not found in $PATH\"\n".
```
我们可以看到可执行文件找不到的报错，executable file not found。之前我们说过，跟在镜像名后面的是 command，运行时会替换 CMD 的默认值。因此这里的 -i 替换了原来的 CMD，而不是添加在原来的 curl -s http://myip.ipip.net 后面。而 -i 根本不是命令，所以自然找不到。

那么如果我们希望加入 -i 这参数，我们就必须重新完整的输入这个命令：

```bash
$ docker run myip curl -s http://myip.ipip.net -i
```
这显然不是很好的解决方案，而使用 ENTRYPOINT 就可以解决这个问题。现在我们重新用 ENTRYPOINT 来实现这个镜像：

```bash
FROM ubuntu:18.04
RUN apt-get update \
    && apt-get install -y curl \
    && rm -rf /var/lib/apt/lists/*
ENTRYPOINT [ "curl", "-s", "http://myip.ipip.net" ]
```
这次我们再来尝试直接使用 docker run myip -i：

```bash 
$ docker run myip
当前 IP：61.148.226.66 来自：北京市 联通

$ docker run myip -i
HTTP/1.1 200 OK
Server: nginx/1.8.0
Date: Tue, 22 Nov 2016 05:12:40 GMT
Content-Type: text/html; charset=UTF-8
Vary: Accept-Encoding
X-Powered-By: PHP/5.6.24-1~dotdeb+7.1
X-Cache: MISS from cache-2
X-Cache-Lookup: MISS from cache-2:80
X-Cache: MISS from proxy-2_6
Transfer-Encoding: chunked
Via: 1.1 cache-2:80, 1.1 proxy-2_6:8006
Connection: keep-alive
```

当前 IP：61.148.226.66 来自：北京市 联通
可以看到，这次成功了。这是因为当存在 ENTRYPOINT 后，CMD 的内容将会作为参数传给 ENTRYPOINT，而这里 -i 就是新的 CMD，因此会作为参数传给 curl，从而达到了我们预期的效果。

### 场景二：应用运行前的准备工作

启动容器就是启动主进程，但有些时候，启动主进程前，需要一些准备工作。

比如 mysql 类的数据库，可能需要一些数据库配置、初始化的工作，这些工作要在最终的 mysql 服务器运行之前解决。

此外，可能希望避免使用 root 用户去启动服务，从而提高安全性，而在启动服务前还需要以 root 身份执行一些必要的准备工作，最后切换到服务用户身份启动服务。或者除了服务外，其它命令依旧可以使用 root 身份执行，方便调试等。

这些准备工作是和容器 CMD 无关的，无论 CMD 为什么，都需要事先进行一个预处理的工作。这种情况下，可以写一个脚本，然后放入 ENTRYPOINT 中去执行，而这个脚本会将接到的参数（也就是 <CMD>）作为命令，在脚本最后执行。比如官方镜像 redis 中就是这么做的：

```bash
FROM alpine:3.4
...
RUN addgroup -S redis && adduser -S -G redis redis
...
ENTRYPOINT ["docker-entrypoint.sh"]

EXPOSE 6379
CMD [ "redis-server" ]
```

可以看到其中为了 redis 服务创建了 redis 用户，并在最后指定了 ENTRYPOINT 为 docker-entrypoint.sh 脚本。

```bash
#!/bin/sh
...
# allow the container to be started with `--user`
if [ "$1" = 'redis-server' -a "$(id -u)" = '0' ]; then
	find . \! -user redis -exec chown redis '{}' +
	exec gosu redis "$0" "$@"
fi

exec "$@"
```

该脚本的内容就是根据 CMD 的内容来判断，如果是 redis-server 的话，则切换到 redis 用户身份启动服务器，否则依旧使用 root 身份执行。比如：

```bash
$ docker run -it redis id
uid=0(root) gid=0(root) groups=0(root)
```

### CMD

CMD 指令用于指定容器启动时要运行的命令。

CMD 在docker run 时运行。
RUN 是在 docker build。

> 注意：如果 Dockerfile 中如果存在多个 CMD 指令，仅最后一个生效。

```bash
CMD <shell 命令> 
CMD ["<可执行文件或命令>","<param1>","<param2>",...] 
CMD ["<param1>","<param2>",...]  # 该写法是为 ENTRYPOINT 指令指定的程序提供默认参数
```
推荐使用第二种格式，执行过程比较明确。第一种格式实际上在运行的过程中也会自动转换成第二种格式运行，并且默认可执行文件是 sh


之前介绍容器的时候曾经说过，Docker 不是虚拟机，容器就是进程。既然是进程，那么在启动容器的时候，需要指定所运行的程序及参数。CMD 指令就是用于指定默认的容器主进程的启动命令的。

在运行时可以指定新的命令来替代镜像设置中的这个默认命令，比如，ubuntu 镜像默认的 CMD 是 /bin/bash，如果我们直接 docker run -it ubuntu 的话，会直接进入 bash。我们也可以在运行时指定运行别的命令，如 docker run -it ubuntu cat /etc/os-release。这就是用 cat /etc/os-release 命令替换了默认的 /bin/bash 命令了，输出了系统版本信息。

在指令格式上，一般推荐使用 exec 格式，这类格式在解析时会被解析为 JSON 数组，因此一定要使用双引号 "，而不要使用单引号。

如果使用 shell 格式的话，实际的命令会被包装为 sh -c 的参数的形式进行执行。比如：

```bash
CMD echo $HOME
```
在实际执行中，会将其变更为：

```bash
CMD [ "sh", "-c", "echo $HOME" ]
```
这就是为什么我们可以使用环境变量的原因，因为这些环境变量会被 shell 进行解析处理。

提到 CMD 就不得不提容器中应用在前台执行和后台执行的问题。这是初学者常出现的一个混淆。

Docker 不是虚拟机，容器中的应用都应该以前台执行，而不是像虚拟机、物理机里面那样，用 systemd 去启动后台服务，容器内没有后台服务的概念。

一些初学者将 CMD 写为：

```bash
CMD service nginx start
```
然后发现容器执行后就立即退出了。甚至在容器内去使用 systemctl 命令结果却发现根本执行不了。这就是因为没有搞明白前台、后台的概念，没有区分容器和虚拟机的差异，依旧在以传统虚拟机的角度去理解容器。

对于容器而言，其启动程序就是容器应用进程，容器就是为了主进程而存在的，主进程退出，容器就失去了存在的意义，从而退出，其它辅助进程不是它需要关心的东西。

而使用 service nginx start 命令，则是希望 init 系统以后台守护进程的形式启动 nginx 服务。而刚才说了 CMD service nginx start 会被理解为 CMD [ "sh", "-c", "service nginx start"]，因此主进程实际上是 sh。那么当 service nginx start 命令结束后，sh 也就结束了，sh 作为主进程退出了，自然就会令容器退出。

正确的做法是直接执行 nginx 可执行文件，并且要求以前台形式运行。比如：

```bash
CMD ["nginx", "-g", "daemon off;"]
```



### ENTRYPOINT

ENTRYPOINT 指令用于指定容器启动时要运行的命令，与 CMD 指令不同的是，ENTRYPOINT 指令指定的命令不会被 docker run 命令后面的参数覆盖。

但是, 如果运行 docker run 时使用了 --entrypoint 选项，将覆盖 ENTRYPOINT 指令指定的程序。

优点：在执行 docker run 的时候可以指定 ENTRYPOINT 运行所需的参数。

注意：如果 Dockerfile 中如果存在多个 ENTRYPOINT 指令，仅最后一个生效。

格式：

```bash
ENTRYPOINT ["<executeable>","<param1>","<param2>",...]
```

可以搭配 CMD 命令使用：一般是变参才会使用 CMD ，这里的 CMD 等于是在给 ENTRYPOINT 传参，以下示例会提到。

示例：
```bash
FROM nginx

ENTRYPOINT ["nginx", "-c"] # 定参
CMD ["/etc/nginx/nginx.conf"] # 变参 
```
1、不传参运行

```bash
docker run  nginx:test
```

容器内会默认运行以下命令，启动主进程。

```bash
nginx -c /etc/nginx/nginx.conf
```

2、传参运行
```bash
docker run  nginx:test -c /etc/nginx/new.conf
```

容器内会默认运行以下命令，启动主进程(/etc/nginx/new.conf:假设容器内已有此文件)

```bash
nginx -c /etc/nginx/new.conf
```

### ENV

ENV 指令用于设置环境变量。

格式有两种：
```bash
ENV <key> <value>

ENV <key1>=<value1> <key2>=<value2>...
```

这个指令很简单，就是设置环境变量而已，无论是后面的其它指令，如 RUN，还是运行时的应用，都可以直接使用这里定义的环境变量。

```bash
ENV VERSION=1.0 DEBUG=on \
    NAME="Happy Feet"
```

这个例子中演示了如何换行，以及对含有空格的值用双引号括起来的办法，这和 Shell 下的行为是一致的。

定义了环境变量，那么在后续的指令中，就可以使用这个环境变量。比如在官方 node 镜像 Dockerfile 中，就有类似这样的代码：

```bash
ENV NODE_VERSION 7.2.0

RUN curl -SLO "https://nodejs.org/dist/v$NODE_VERSION/node-v$NODE_VERSION-linux-x64.tar.xz" \
  && curl -SLO "https://nodejs.org/dist/v$NODE_VERSION/SHASUMS256.txt.asc" \
  && gpg --batch --decrypt --output SHASUMS256.txt SHASUMS256.txt.asc \
  && grep " node-v$NODE_VERSION-linux-x64.tar.xz\$" SHASUMS256.txt | sha256sum -c - \
  && tar -xJf "node-v$NODE_VERSION-linux-x64.tar.xz" -C /usr/local --strip-components=1 \
  && rm "node-v$NODE_VERSION-linux-x64.tar.xz" SHASUMS256.txt.asc SHASUMS256.txt \
  && ln -s /usr/local/bin/node /usr/local/bin/nodejs
```

在这里先定义了环境变量 NODE_VERSION，其后的 RUN 这层里，多次使用 $NODE_VERSION 来进行操作定制。可以看到，将来升级镜像构建版本的时候，只需要更新 7.2.0 即可，Dockerfile 构建维护变得更轻松了。

下列指令可以支持环境变量展开： ADD、COPY、ENV、EXPOSE、FROM、LABEL、USER、WORKDIR、VOLUME、STOPSIGNAL、ONBUILD、RUN。

可以从这个指令列表里感觉到，环境变量可以使用的地方很多，很强大。通过环境变量，我们可以让一份 Dockerfile 制作更多的镜像，只需使用不同的环境变量即可。

### ARG

ARG 指令用于定义构建时的参数。 ENV 作用一致。不过作用域不一样。ARG 设置的环境变量仅对 Dockerfile 内有效，也就是说只有 docker build 的过程中有效，构建好的镜像内不存在此环境变量。

构建命令 docker build 中可以用 --build-arg <参数名>=<值> 来覆盖。

格式：
```bash
ARG <参数名>[=<默认值>]
```

构建参数和 ENV 的效果一样，都是设置环境变量。所不同的是，ARG 所设置的构建环境的环境变量，在将来容器运行时是不会存在这些环境变量的。但是不要因此就使用 ARG 保存密码之类的信息，因为 docker history 还是可以看到所有值的。

Dockerfile 中的 ARG 指令是定义参数名称，以及定义其默认值。该默认值可以在构建命令 docker build 中用 --build-arg <参数名>=<值> 来覆盖。

灵活的使用 ARG 指令，能够在不修改 Dockerfile 的情况下，构建出不同的镜像。

ARG 指令有生效范围，如果在 FROM 指令之前指定，那么只能用于 FROM 指令中。

```bash
ARG DOCKER_USERNAME=library

FROM ${DOCKER_USERNAME}/alpine

RUN set -x ; echo ${DOCKER_USERNAME}
```

使用上述 Dockerfile 会发现无法输出 ${DOCKER_USERNAME} 变量的值，要想正常输出，你必须在 FROM 之后再次指定 ARG

```bash
# 只在 FROM 中生效
ARG DOCKER_USERNAME=library

FROM ${DOCKER_USERNAME}/alpine

# 要想在 FROM 之后使用，必须再次指定
ARG DOCKER_USERNAME=library

RUN set -x ; echo ${DOCKER_USERNAME}
```

对于多阶段构建，尤其要注意这个问题

```bash
# 这个变量在每个 FROM 中都生效
ARG DOCKER_USERNAME=library

FROM ${DOCKER_USERNAME}/alpine

RUN set -x ; echo 1

FROM ${DOCKER_USERNAME}/alpine

RUN set -x ; echo 2
```

对于上述 Dockerfile 两个 FROM 指令都可以使用 ${DOCKER_USERNAME}，对于在各个阶段中使用的变量都必须在每个阶段分别指定：

```bash
ARG DOCKER_USERNAME=library

FROM ${DOCKER_USERNAME}/alpine

# 在FROM 之后使用变量，必须在每个阶段分别指定
ARG DOCKER_USERNAME=library

RUN set -x ; echo ${DOCKER_USERNAME}

FROM ${DOCKER_USERNAME}/alpine

# 在FROM 之后使用变量，必须在每个阶段分别指定
ARG DOCKER_USERNAME=library

RUN set -x ; echo ${DOCKER_USERNAME}
```


### VOLUME

VOLUME 指令用于指定容器中的目录作为卷挂载到主机上。
作用：

避免重要的数据，因容器重启而丢失，这是非常致命的。
避免容器不断变大。
格式：

```bash
VOLUME ["<路径1>", "<路径2>"...]
VOLUME <路径>
```

在启动容器 docker run 的时候，我们可以通过 -v 参数修改挂载点
比如：

```bash
$ docker run -d -v $PWD/html:/usr/share/nginx/html nginx
```
上面的实例是将当前目录下的 html 目录挂载到容器的 /usr/share/nginx/html 目录。

docker run -volumes-from <容器名>


### EXPOSE
仅仅只是声明端口。

作用：

帮助镜像使用者理解这个镜像服务的守护端口，以方便配置映射。
在运行时使用随机端口映射时，也就是 docker run -P 时，会自动随机映射 EXPOSE 的端口。
格式：

```bash
EXPOSE <端口1> [<端口2>...]
```
EXPOSE 指令是声明容器运行时提供服务的端口，这只是一个声明，在容器运行时并不会因为这个声明应用就会开启这个端口的服务。在 Dockerfile 中写入这样的声明有两个好处，一个是帮助镜像使用者理解这个镜像服务的守护端口，以方便配置映射；另一个用处则是在运行时使用随机端口映射时，也就是 docker run -P 时，会自动随机映射 EXPOSE 的端口。

要将 EXPOSE 和在运行时使用 -p <宿主端口>:<容器端口> 区分开来。-p，是映射宿主端口和容器端口，换句话说，就是将容器的对应端口服务公开给外界访问，而 EXPOSE 仅仅是声明容器打算使用什么端口而已，并不会自动在宿主进行端口映射。


### WORKDIR


使用 WORKDIR 指令可以来指定工作目录（或者称为当前目录），以后各层的当前目录就被改为指定的目录，如该目录不存在，WORKDIR 会帮你建立目录。

docker build 构建镜像过程中的，每一个 RUN 命令都是新建的一层。只有通过 WORKDIR 创建的目录才会一直存在。

格式：
```bash
WORKDIR <工作目录路径>
```



之前提到一些初学者常犯的错误是把 Dockerfile 等同于 Shell 脚本来书写，这种错误的理解还可能会导致出现下面这样的错误：

```bash
RUN cd /app
RUN echo "hello" > world.txt
```

如果将这个 Dockerfile 进行构建镜像运行后，会发现找不到 /app/world.txt 文件，或者其内容不是 hello。原因其实很简单，在 Shell 中，连续两行是同一个进程执行环境，因此前一个命令修改的内存状态，会直接影响后一个命令；而在 Dockerfile 中，这两行 RUN 命令的执行环境根本不同，是两个完全不同的容器。这就是对 Dockerfile 构建分层存储的概念不了解所导致的错误。

之前说过每一个 RUN 都是启动一个容器、执行命令、然后提交存储层文件变更。第一层 RUN cd /app 的执行仅仅是当前进程的工作目录变更，一个内存上的变化而已，其结果不会造成任何文件变更。而到第二层的时候，启动的是一个全新的容器，跟第一层的容器更完全没关系，自然不可能继承前一层构建过程中的内存变化。

因此如果需要改变以后各层的工作目录的位置，那么应该使用 WORKDIR 指令。

```bash
WORKDIR /app

RUN echo "hello" > world.txt
```

如果你的 WORKDIR 指令使用的相对路径，那么所切换的路径与之前的 WORKDIR 有关：

```bash
WORKDIR /a
WORKDIR b
WORKDIR c
RUN pwd
```

RUN pwd 的工作目录为 /a/b/c。

### USER

用于指定执行后续命令的用户和用户组，这边只是切换后续命令执行的用户（用户和用户组必须提前已经存在）。

格式：
```bash
USER <用户名>[:<用户组>]
```

USER 指令和 WORKDIR 相似，都是改变环境状态并影响以后的层。WORKDIR 是改变工作目录，USER 则是改变之后层的执行 RUN, CMD 以及 ENTRYPOINT 这类命令的身份。

注意，USER 只是帮助你切换到指定用户而已，这个用户必须是事先建立好的，否则无法切换。

```bash
RUN groupadd -r redis && useradd -r -g redis redis
USER redis
RUN [ "redis-server" ]
```

如果以 root 执行的脚本，在执行期间希望改变身份，比如希望以某个已经建立好的用户来运行某个服务进程，不要使用 su 或者 sudo，这些都需要比较麻烦的配置，而且在 TTY 缺失的环境下经常出错。建议使用 gosu。

```bash
# 建立 redis 用户，并使用 gosu 换另一个用户执行命令
RUN groupadd -r redis && useradd -r -g redis redis
# 下载 gosu
RUN wget -O /usr/local/bin/gosu "https://github.com/tianon/gosu/releases/download/1.12/gosu-amd64" \
    && chmod +x /usr/local/bin/gosu \
    && gosu nobody true
# 设置 CMD，并以另外的用户执行
CMD [ "exec", "gosu", "redis", "redis-server" ]
```


### HEALTHCHECK
用于指定某个程序或者指令来监控 docker 容器服务的运行状态。


格式：
```bash
HEALTHCHECK [选项] CMD <命令>：设置检查容器健康状况的命令
HEALTHCHECK NONE：如果基础镜像有健康检查指令，使用这行可以屏蔽掉其健康检查指令
```

HEALTHCHECK [选项] CMD <命令> : 这边 CMD 后面跟随的命令使用，可以参考 CMD 的用法。

HEALTHCHECK 指令是告诉 Docker 应该如何进行判断容器的状态是否正常，这是 Docker 1.12 引入的新指令。

在没有 HEALTHCHECK 指令前，Docker 引擎只可以通过容器内主进程是否退出来判断容器是否状态异常。很多情况下这没问题，但是如果程序进入死锁状态，或者死循环状态，应用进程并不退出，但是该容器已经无法提供服务了。在 1.12 以前，Docker 不会检测到容器的这种状态，从而不会重新调度，导致可能会有部分容器已经无法提供服务了却还在接受用户请求。

而自 1.12 之后，Docker 提供了 HEALTHCHECK 指令，通过该指令指定一行命令，用这行命令来判断容器主进程的服务状态是否还正常，从而比较真实的反应容器实际状态。

当在一个镜像指定了 HEALTHCHECK 指令后，用其启动容器，初始状态会为 starting，在 HEALTHCHECK 指令检查成功后变为 healthy，如果连续一定次数失败，则会变为 unhealthy。

HEALTHCHECK 支持下列选项：

--interval=<间隔>：两次健康检查的间隔，默认为 30 秒；

--timeout=<时长>：健康检查命令运行超时时间，如果超过这个时间，本次健康检查就被视为失败，默认 30 秒；

--retries=<次数>：当连续失败指定次数后，则将容器状态视为 unhealthy，默认 3 次。

和 CMD, ENTRYPOINT 一样，HEALTHCHECK 只可以出现一次，如果写了多个，只有最后一个生效。

在 HEALTHCHECK [选项] CMD 后面的命令，格式和 ENTRYPOINT 一样，分为 shell 格式，和 exec 格式。命令的返回值决定了该次健康检查的成功与否：0：成功；1：失败；2：保留，不要使用这个值。

假设我们有个镜像是个最简单的 Web 服务，我们希望增加健康检查来判断其 Web 服务是否在正常工作，我们可以用 curl 来帮助判断，其 Dockerfile 的 HEALTHCHECK 可以这么写：

```bash
FROM nginx
RUN apt-get update && apt-get install -y curl && rm -rf /var/lib/apt/lists/*
HEALTHCHECK --interval=5s --timeout=3s \
  CMD curl -fs http://localhost/ || exit 1
```

这里我们设置了每 5 秒检查一次（这里为了试验所以间隔非常短，实际应该相对较长），如果健康检查命令超过 3 秒没响应就视为失败，并且使用 curl -fs http://localhost/ || exit 1 作为健康检查命令。

使用 docker build 来构建这个镜像：

```bash
$ docker build -t myweb:v1
```

构建好了后，我们启动一个容器：

```bash
$ docker run -d --name web -p 80:80 myweb:v1
```

当运行该镜像后，可以通过 docker container ls 看到最初的状态为 (health: starting)：

```bash
$ docker container ls
CONTAINER ID        IMAGE               COMMAND                  CREATED             STATUS                            PORTS               NAMES
03e28eb00bd0        myweb:v1            "nginx -g 'daemon off"   3 seconds ago       Up 2 seconds (health: starting)   80/tcp, 443/tcp     web
```
在等待几秒钟后，再次 docker container ls，就会看到健康状态变化为了 (healthy)：

```bash
$ docker container ls
CONTAINER ID        IMAGE               COMMAND                  CREATED             STATUS                    PORTS               NAMES
03e28eb00bd0        myweb:v1            "nginx -g 'daemon off"   18 seconds ago      Up 16 seconds (healthy)   80/tcp, 443/tcp     web
```

如果健康检查连续失败超过了重试次数，状态就会变为 (unhealthy)。

为了帮助排障，健康检查命令的输出（包括 stdout 以及 stderr）都会被存储于健康状态里，可以用 docker inspect 来查看。

```bash
$ docker inspect --format '{{json .State.Health}}' web | python -m json.tool
{
    "FailingStreak": 0,
    "Log": [
        {
            "End": "2016-11-25T14:35:37.940957051Z",
            "ExitCode": 0,
            "Output": "<!DOCTYPE html>\n<html>\n<head>\n<title>Welcome to nginx!</title>\n<style>\n    body {\n        width: 35em;\n        margin: 0 auto;\n        font-family: Tahoma, Verdana, Arial, sans-serif;\n    }\n</style>\n</head>\n<body>\n<h1>Welcome to nginx!</h1>\n<p>If you see this page, the nginx web server is successfully installed and\nworking. Further configuration is required.</p>\n\n<p>For online documentation and support please refer to\n<a href=\"http://nginx.org/\">nginx.org</a>.<br/>\nCommercial support is available at\n<a href=\"http://nginx.com/\">nginx.com</a>.</p>\n\n<p><em>Thank you for using nginx.</em></p>\n</body>\n</html>\n",
            "Start": "2016-11-25T14:35:37.780192565Z"
        }
    ],
    "Status": "healthy"
}
```



### ONBUILD
用于延迟构建命令的执行。简单的说，就是 Dockerfile 里用 ONBUILD 指定的命令，在本次构建镜像的过程中不会执行（假设镜像为 test-build）。当有新的 Dockerfile 使用了之前构建的镜像 FROM test-build ，这时执行新镜像的 Dockerfile 构建时候，会执行 test-build 的 Dockerfile 里的 ONBUILD 指定的命令。

格式：
```bash
ONBUILD <其它指令>
```
### LABEL
LABEL 指令用来给镜像添加一些元数据（metadata），以键值对的形式，语法格式如下：

```bash
LABEL <key>=<value> <key>=<value> <key>=<value> ...
```

比如我们可以添加镜像的作者：
```bash
LABEL org.opencontainers.image.authors="attax"
```

### SHELL 指令

格式：SHELL ["executable", "parameters"]


SHELL 指令可以指定 RUN ENTRYPOINT CMD 指令的 shell，Linux 中默认为 ["/bin/sh", "-c"]

```bash
SHELL ["/bin/sh", "-c"]

RUN lll ; ls

SHELL ["/bin/sh", "-cex"]

RUN lll ; ls
```

两个 RUN 运行同一命令，第二个 RUN 运行的命令会打印出每条命令并当遇到错误时退出。

当 ENTRYPOINT CMD 以 shell 格式指定时，SHELL 指令所指定的 shell 也会成为这两个指令的 shell

```bash
SHELL ["/bin/sh", "-cex"]

# /bin/sh -cex "nginx"
ENTRYPOINT nginx
```

```bash
SHELL ["/bin/sh", "-cex"]

# /bin/sh -cex "nginx"
CMD nginx
```

---

## Dockerfile多阶段构建

在 Docker 17.05 版本之前，我们构建 Docker 镜像时，通常会采用两种方式：

### 全部放入一个 Dockerfile
一种方式是将所有的构建过程编包含在一个 Dockerfile 中，包括项目及其依赖库的编译、测试、打包等流程，这里可能会带来的一些问题：

镜像层次多，镜像体积较大，部署时间变长

源代码存在泄露的风险

例如，编写 app.go 文件，该程序输出 Hello World!

```go
package main

import "fmt"

func main(){
    fmt.Printf("Hello World!");
}
```

编写 Dockerfile.one 文件

```bash
FROM golang:alpine

RUN apk --no-cache add git ca-certificates

WORKDIR /go/src/github.com/go/helloworld/

COPY app.go .

RUN go mod init helloworld \
  && go get -d -v github.com/go-sql-driver/mysql \
  && CGO_ENABLED=0 GOOS=linux go build -a -installsuffix cgo -o app . \
  && cp /go/src/github.com/go/helloworld/app /root

WORKDIR /root/

CMD ["./app"]
```

构建镜像

```bash
$ docker build -t go/helloworld:1 -f Dockerfile.one .
```

### 分散到多个 Dockerfile
另一种方式，就是我们事先在一个 Dockerfile 将项目及其依赖库编译测试打包好后，再将其拷贝到运行环境中，这种方式需要我们编写两个 Dockerfile 和一些编译脚本才能将其两个阶段自动整合起来，这种方式虽然可以很好地规避第一种方式存在的风险，但明显部署过程较复杂。

例如，编写 Dockerfile.build 文件

```bash
FROM golang:alpine

RUN apk --no-cache add git

WORKDIR /go/src/github.com/go/helloworld

COPY app.go .

RUN go get -d -v github.com/go-sql-driver/mysql \
  && CGO_ENABLED=0 GOOS=linux go build -a -installsuffix cgo -o app .
```

编写 Dockerfile.copy 文件

```bash
FROM alpine:latest

RUN apk --no-cache add ca-certificates

WORKDIR /root/

COPY app .

CMD ["./app"]
```

新建 build.sh

```bash
#!/bin/sh
echo Building go/helloworld:build

docker build -t go/helloworld:build . -f Dockerfile.build

docker create --name extract go/helloworld:build
docker cp extract:/go/src/github.com/go/helloworld/app ./app
docker rm -f extract

echo Building go/helloworld:2

docker build --no-cache -t go/helloworld:2 . -f Dockerfile.copy
rm ./app
```

现在运行脚本即可构建镜像

```bash
$ chmod +x build.sh

$ ./build.sh
```

对比两种方式生成的镜像大小

```bash
$ docker image ls

REPOSITORY      TAG    IMAGE ID        CREATED         SIZE
go/helloworld   2      f7cf3465432c    22 seconds ago  6.47MB
go/helloworld   1      f55d3e16affc    2 minutes ago   295MB
```


### 使用多阶段构建
为解决以上问题，Docker v17.05 开始支持多阶段构建 (multistage builds)。使用多阶段构建我们就可以很容易解决前面提到的问题，并且只需要编写一个 Dockerfile：

例如，编写 Dockerfile 文件

```bash
FROM golang:alpine as builder

RUN apk --no-cache add git

WORKDIR /go/src/github.com/go/helloworld/

RUN go get -d -v github.com/go-sql-driver/mysql

COPY app.go .

RUN CGO_ENABLED=0 GOOS=linux go build -a -installsuffix cgo -o app .

FROM alpine:latest as prod

RUN apk --no-cache add ca-certificates

WORKDIR /root/

COPY --from=0 /go/src/github.com/go/helloworld/app .

CMD ["./app"]
```
构建镜像

```bash
$ docker build -t go/helloworld:3 .
```

对比三个镜像大小

```bash
$ docker image ls

REPOSITORY        TAG   IMAGE ID         CREATED            SIZE
go/helloworld     3     d6911ed9c846     7 seconds ago      6.47MB
go/helloworld     2     f7cf3465432c     22 seconds ago     6.47MB
go/helloworld     1     f55d3e16affc     2 minutes ago      295MB
```

很明显使用多阶段构建的镜像体积小，同时也完美解决了上边提到的问题。

### 只构建某一阶段的镜像

我们可以使用 as 来为某一阶段命名，例如

```bash
FROM golang:alpine as builder
```

例如当我们只想构建 builder 阶段的镜像时，增加 --target=builder 参数即可

```bash
$ docker build --target builder -t username/imagename:tag .
```

### 构建时从其他镜像复制文件
上面例子中我们使用 COPY --from=0 /go/src/github.com/go/helloworld/app . 从上一阶段的镜像中复制文件，我们也可以复制任意镜像中的文件。

```bash
$ COPY --from=nginx:latest /etc/nginx/nginx.conf /nginx.conf
```



---

## 实战多阶段构建 Laravel 镜像

本节适用于 PHP 开发者阅读。Laravel 基于 8.x 版本，各个版本的文件结构可能会有差异，请根据实际自行修改。

### 准备
新建一个 Laravel 项目或在已有的 Laravel 项目根目录下新建 Dockerfile .dockerignore laravel.conf 文件。

在 .dockerignore 文件中写入以下内容。

```bash
.idea/
.git/

vendor/

node_modules/

public/js/
public/css/
public/mix-manifest.json

yarn-error.log

bootstrap/cache/*
storage/

# 自行添加其他需要排除的文件，例如 .env.* 文件
```

在 laravel.conf 文件中写入 nginx 配置。

```bash
server {
  listen 80 default_server;
  root /app/laravel/public;
  index index.php index.html;

  location / {
      try_files $uri $uri/ /index.php?$query_string;
  }

  location ~ .*\.php(\/.*)*$ {
    fastcgi_pass   laravel:9000;
    include        fastcgi.conf;

    # fastcgi_connect_timeout 300;
    # fastcgi_send_timeout 300;
    # fastcgi_read_timeout 300;
  }
}
```

### 前端构建

第一阶段进行前端构建。

```bash
FROM node:alpine as frontend

COPY package.json /app/

RUN set -x ; cd /app \
      && npm install --registry=https://registry.npmmirror.com

COPY webpack.mix.js webpack.config.js tailwind.config.js /app/
COPY resources/ /app/resources/

RUN set -x ; cd /app \
      && touch artisan \
      && mkdir -p public \
      && npm run production
```

安装 Composer 依赖
第二阶段安装 Composer 依赖。

```bash
FROM composer as composer

COPY database/ /app/database/
COPY composer.json composer.lock /app/

RUN set -x ; cd /app \
      && composer config -g repo.packagist composer https://mirrors.aliyun.com/composer/ \
      && composer install \
           --ignore-platform-reqs \
           --no-interaction \
           --no-plugins \
           --no-scripts \
           --prefer-dist
```

整合以上阶段所生成的文件
第三阶段对以上阶段生成的文件进行整合。

```bash
FROM php:7.4-fpm-alpine as laravel

ARG LARAVEL_PATH=/app/laravel

COPY --from=composer /app/vendor/ ${LARAVEL_PATH}/vendor/
COPY . ${LARAVEL_PATH}
COPY --from=frontend /app/public/js/ ${LARAVEL_PATH}/public/js/
COPY --from=frontend /app/public/css/ ${LARAVEL_PATH}/public/css/
COPY --from=frontend /app/public/mix-manifest.json ${LARAVEL_PATH}/public/mix-manifest.json

RUN set -x ; cd ${LARAVEL_PATH} \
      && mkdir -p storage \
      && mkdir -p storage/framework/cache \
      && mkdir -p storage/framework/sessions \
      && mkdir -p storage/framework/testing \
      && mkdir -p storage/framework/views \
      && mkdir -p storage/logs \
      && chmod -R 777 storage \
      && php artisan package:discover
```

最后一个阶段构建 NGINX 镜像

```bash
FROM nginx:alpine as nginx

ARG LARAVEL_PATH=/app/laravel

COPY laravel.conf /etc/nginx/conf.d/
COPY --from=laravel ${LARAVEL_PATH}/public ${LARAVEL_PATH}/public
```

构建 Laravel 及 Nginx 镜像
使用 docker build 命令构建镜像。

```bash
$ docker build -t my/laravel --target=laravel .

$ docker build -t my/nginx --target=nginx .
```

### 启动容器并测试

新建 Docker 网络

```bash
$ docker network create laravel
```

启动 laravel 容器， --name=laravel 参数设定的名字必须与 nginx 配置文件中的 fastcgi_pass laravel:9000; 一致

```bash
$ docker run -dit --rm --name=laravel --network=laravel my/laravel
```

启动 nginx 容器

```bash
$ docker run -dit --rm --network=laravel -p 8080:80 my/nginx
```

浏览器访问 127.0.0.1:8080 可以看到 Laravel 项目首页。

也许 Laravel 项目依赖其他外部服务，例如 redis、MySQL，请自行启动这些服务之后再进行测试，本小节不再赘述。

### 生产环境优化

本小节内容为了方便测试，将配置文件直接放到了镜像中，实际在使用时 建议 将配置文件作为 config 或 secret 挂载到容器中，请读者自行学习 Swarm mode 或 Kubernetes 的相关内容。

由于篇幅所限本小节只是简单列出，更多内容可以参考 https://github.com/khs1994-docker/laravel-demo 项目。

### 附录
完整的 Dockerfile 文件如下。

```bash
FROM node:alpine as frontend

COPY package.json /app/

RUN set -x ; cd /app \
      && npm install --registry=https://registry.npmmirror.com

COPY webpack.mix.js webpack.config.js tailwind.config.js /app/
COPY resources/ /app/resources/

RUN set -x ; cd /app \
      && touch artisan \
      && mkdir -p public \
      && npm run production

FROM composer as composer

COPY database/ /app/database/
COPY composer.json /app/

RUN set -x ; cd /app \
      && composer config -g repo.packagist composer https://mirrors.aliyun.com/composer/ \
      && composer install \
           --ignore-platform-reqs \
           --no-interaction \
           --no-plugins \
           --no-scripts \
           --prefer-dist

FROM php:7.4-fpm-alpine as laravel

ARG LARAVEL_PATH=/app/laravel

COPY --from=composer /app/vendor/ ${LARAVEL_PATH}/vendor/
COPY . ${LARAVEL_PATH}
COPY --from=frontend /app/public/js/ ${LARAVEL_PATH}/public/js/
COPY --from=frontend /app/public/css/ ${LARAVEL_PATH}/public/css/
COPY --from=frontend /app/public/mix-manifest.json ${LARAVEL_PATH}/public/mix-manifest.json

RUN set -x ; cd ${LARAVEL_PATH} \
      && mkdir -p storage \
      && mkdir -p storage/framework/cache \
      && mkdir -p storage/framework/sessions \
      && mkdir -p storage/framework/testing \
      && mkdir -p storage/framework/views \
      && mkdir -p storage/logs \
      && chmod -R 777 storage \
      && php artisan package:discover

FROM nginx:alpine as nginx

ARG LARAVEL_PATH=/app/laravel

COPY laravel.conf /etc/nginx/conf.d/
COPY --from=laravel ${LARAVEL_PATH}/public ${LARAVEL_PATH}/public
```

---


## Docker Compose

Compose 项目是 Docker 官方的开源项目，负责实现对 Docker 容器集群的快速编排。

Compose 前身是开源项目 Fig，定位是 「定义和运行多个 Docker 容器的应用（Defining and running multi-container Docker applications）」，用于定义和运行多容器 Docker 应用程序。通过 Compose，您可以使用 YAML 文件来配置应用程序需要的所有服务。然后，使用一个命令，就可以从 YAML 文件配置中创建并启动所有服务。


使用一个 Dockerfile 模板文件，可以让用户很方便的定义一个单独的应用容器。然而，在日常工作中，经常会碰到需要多个容器相互配合来完成某项任务的情况。例如要实现一个 Web 项目，除了 Web 服务容器本身，往往还需要再加上后端的数据库服务容器，甚至还包括负载均衡容器等。

Compose 恰好满足了这样的需求。它允许用户通过一个单独的 docker-compose.yml 模板文件（YAML 格式）来定义一组相关联的应用容器为一个项目（project）。

Compose 中有两个重要的概念：

> 服务 (service)：一个应用的容器，实际上可以包括若干运行相同镜像的容器实例。
> 项目 (project)：由一组关联的应用容器组成的一个完整业务单元，在 docker-compose.yml 文件中定义。

Compose 的默认管理对象是项目，通过子命令对项目中的一组容器进行便捷地生命周期管理。

Compose 使用的三个步骤：

1. 使用 Dockerfile 定义应用程序的环境。

2. 使用 docker-compose.yml 定义构成应用程序的服务，这样它们可以在隔离环境中一起运行。

3. 执行 docker-compose up 命令来启动并运行整个应用程序。

docker-compose.yml 的配置案例如下（配置参数参考下文）：

```yaml
version: '3'
services:
  web:
    build: .
    ports:
   - "5000:5000"
    volumes:
   - .:/code
    - logvolume01:/var/log
    links:
   - redis
  redis:
    image: redis
volumes:
  logvolume01: {}
```


---

## Compose 模板文件

模板文件是使用 Compose 的核心，大部分指令跟 docker run 相关参数的含义都是类似的。

默认的模板文件名称为 docker-compose.yml，格式为 YAML 格式。

```yaml
version: "3"

services:
  webapp:
    image: examples/web
    ports:
      - "80:80"
    volumes:
      - "/data"
```

> 注意：每个服务都必须通过 image 指令指定镜像或 build 指令（需要 Dockerfile）等来自动构建生成镜像。

如果使用 build 指令，在 Dockerfile 中设置的选项(例如：CMD, EXPOSE, VOLUME, ENV 等) 将会自动被获取，无需在 docker-compose.yml 中重复设置。

### 配置指令的用法

build
指定 Dockerfile 所在文件夹的路径（可以是绝对路径，或者相对 docker-compose.yml 文件的路径）。 Compose 将会利用它自动构建这个镜像，然后使用这个镜像。

```yaml
version: '3'
services:
  webapp:
    build: ./dir
```
也可以使用 context 指令指定 Dockerfile 所在文件夹的路径。

使用 dockerfile 指令指定 Dockerfile 文件名。

使用 arg 指令指定构建镜像时的变量。

```yaml
version: '3'
services:

  webapp:
    build:
      context: ./dir
      dockerfile: Dockerfile-alternate
      args:
        buildno: 1
```
使用 cache_from 指定构建镜像的缓存

```yaml
build:
  context: .
  cache_from:
    - alpine:latest
    - corp/web_app:3.14
```

cap_add, cap_drop
指定容器的内核能力（capacity）分配。

例如，让容器拥有所有能力可以指定为：

```yaml
cap_add:
  - ALL
```

去掉 NET_ADMIN 能力可以指定为：

```yaml
cap_drop:
  - NET_ADMIN
```
command
覆盖容器启动后默认执行的命令。

```yaml
command: echo "hello world"
```
configs
仅用于 Swarm mode，详细内容请查看 Swarm mode 一节。

cgroup_parent
指定父 cgroup 组，意味着将继承该组的资源限制。

例如，创建了一个 cgroup 组名称为 cgroups_1。

```yaml
cgroup_parent: cgroups_1
```

container_name
指定容器名称。默认将会使用 项目名称_服务名称_序号 这样的格式。

```yaml
container_name: docker-web-container
```

注意: 指定容器名称后，该服务将无法进行扩展（scale），因为 Docker 不允许多个容器具有相同的名称。

deploy
仅用于 Swarm mode，详细内容请查看 Swarm mode 一节

devices
指定设备映射关系。

```yaml
devices:
  - "/dev/ttyUSB1:/dev/ttyUSB0"
```

depends_on
解决容器的依赖、启动先后的问题。以下例子中会先启动 redis db 再启动 web

```yaml
version: '3'

services:
  web:
    build: .
    depends_on:
      - db
      - redis

  redis:
    image: redis

  db:
    image: postgres
```

注意：web 服务不会等待 redis db 「完全启动」之后才启动。

dns
自定义 DNS 服务器。可以是一个值，也可以是一个列表。

```yaml
dns: 8.8.8.8

dns:
  - 8.8.8.8
  - 114.114.114.114
```

dns_search
配置 DNS 搜索域。可以是一个值，也可以是一个列表。

```yaml
dns_search: example.com

dns_search:
  - domain1.example.com
  - domain2.example.com
```

tmpfs
挂载一个 tmpfs 文件系统到容器。

```yaml
tmpfs: /run
tmpfs:
  - /run
  - /tmp
```

env_file
从文件中获取环境变量，可以为单独的文件路径或列表。

如果通过 docker-compose -f FILE 方式来指定 Compose 模板文件，则 env_file 中变量的路径会基于模板文件路径。

如果有变量名称与 environment 指令冲突，则按照惯例，以后者为准。

```yaml
env_file: .env

env_file:
  - ./common.env
  - ./apps/web.env
  - /opt/secrets.env
```

环境变量文件中每一行必须符合格式，支持 # 开头的注释行。

```yaml
# common.env: Set development environment
PROG_ENV=development
```

environment
设置环境变量。你可以使用数组或字典两种格式。

只给定名称的变量会自动获取运行 Compose 主机上对应变量的值，可以用来防止泄露不必要的数据。

```yaml
environment:
  RACK_ENV: development
  SESSION_SECRET:

environment:
  - RACK_ENV=development
  - SESSION_SECRET
```

如果变量名称或者值中用到 true|false，yes|no 等表达 布尔 含义的词汇，最好放到引号里，避免 YAML 自动解析某些内容为对应的布尔语义。这些特定词汇，包括

```yaml
y|Y|yes|Yes|YES|n|N|no|No|NO|true|True|TRUE|false|False|FALSE|on|On|ON|off|Off|OFF
```

expose
暴露端口，但不映射到宿主机，只被连接的服务访问。

仅可以指定内部端口为参数

```yaml
expose:
 - "3000"
 - "8000"
```

external_links
注意：不建议使用该指令。

链接到 docker-compose.yml 外部的容器，甚至并非 Compose 管理的外部容器。

```yaml
external_links:
 - redis_1
 - project_db_1:mysql
 - project_db_1:postgresql
```

extra_hosts
类似 Docker 中的 --add-host 参数，指定额外的 host 名称映射信息。

```yaml
extra_hosts:
 - "googledns:8.8.8.8"
 - "dockerhub:52.1.157.61"
```
会在启动后的服务容器中 /etc/hosts 文件中添加如下两条条目。

```bash
8.8.8.8 googledns
52.1.157.61 dockerhub
```

healthcheck
通过命令检查容器是否健康运行。

```yaml
healthcheck:
  test: ["CMD", "curl", "-f", "http://localhost"]
  interval: 1m30s
  timeout: 10s
  retries: 3
```

image
指定为镜像名称或镜像 ID。如果镜像在本地不存在，Compose 将会尝试拉取这个镜像。

```yaml
image: ubuntu
image: orchardup/postgresql
image: a4bc65fd
```
labels
为容器添加 Docker 元数据（metadata）信息。例如可以为容器添加辅助说明信息。

```yaml
labels:
  com.startupteam.description: "webapp for a startup team"
  com.startupteam.department: "devops department"
  com.startupteam.release: "rc3 for v1.0"
```
links
注意：不推荐使用该指令。

logging
配置日志选项。

```yaml
logging:
  driver: syslog
  options:
    syslog-address: "tcp://192.168.0.42:123"
```
目前支持三种日志驱动类型。

```yaml
driver: "json-file"
driver: "syslog"
driver: "none"
```
options 配置日志驱动的相关参数。

```yaml
options:
  max-size: "200k"
  max-file: "10"
```
network_mode
设置网络模式。使用和 docker run 的 --network 参数一样的值。

```yaml
network_mode: "bridge"
network_mode: "host"
network_mode: "none"
network_mode: "service:[service name]"
network_mode: "container:[container name/id]"
```
networks
配置容器连接的网络。

```yaml
version: "3"
services:

  some-service:
    networks:
     - some-network
     - other-network

networks:
  some-network:
  other-network:
```
pid
跟主机系统共享进程命名空间。打开该选项的容器之间，以及容器和宿主机系统之间可以通过进程 ID 来相互访问和操作。

```yaml
pid: "host"
```
ports
暴露端口信息。

使用宿主端口：容器端口 (HOST:CONTAINER) 格式，或者仅仅指定容器的端口（宿主将会随机选择端口）都可以。

```yaml     
ports:
 - "3000"
 - "8000:8000"
 - "49100:22"
 - "127.0.0.1:8001:8001"
```

注意：当使用 HOST:CONTAINER 格式来映射端口时，如果你使用的容器端口小于 60 并且没放到引号里，可能会得到错误结果，因为 YAML 会自动解析 xx:yy 这种数字格式为 60 进制。为避免出现这种问题，建议数字串都采用引号包括起来的字符串格式。

secrets
存储敏感数据，例如 mysql 服务密码。

```yaml
version: "3.1"
services:

mysql:
  image: mysql
  environment:
    MYSQL_ROOT_PASSWORD_FILE: /run/secrets/db_root_password
  secrets:
    - db_root_password
    - my_other_secret

secrets:
  my_secret:
    file: ./my_secret.txt
  my_other_secret:
    external: true
```

security_opt
指定容器模板标签（label）机制的默认属性（用户、角色、类型、级别等）。例如配置标签的用户名和角色名。

```yaml
security_opt:
    - label:user:USER
    - label:role:ROLE
```
stop_signal
设置另一个信号来停止容器。在默认情况下使用的是 SIGTERM 停止容器。

```yaml
stop_signal: SIGUSR1
```

sysctls
配置容器内核参数。

```yaml
sysctls:
  net.core.somaxconn: 1024
  net.ipv4.tcp_syncookies: 0

sysctls:
  - net.core.somaxconn=1024
  - net.ipv4.tcp_syncookies=0
```

ulimits
指定容器的 ulimits 限制值。

例如，指定最大进程数为 65535，指定文件句柄数为 20000（软限制，应用可以随时修改，不能超过硬限制） 和 40000（系统硬限制，只能 root 用户提高）。

```yaml
  ulimits:
    nproc: 65535
    nofile:
      soft: 20000
      hard: 40000
```

volumes
数据卷所挂载路径设置。可以设置为宿主机路径(HOST:CONTAINER)或者数据卷名称(VOLUME:CONTAINER)，并且可以设置访问模式 （HOST:CONTAINER:ro）。

该指令中路径支持相对路径。

```yaml
volumes:
 - /var/lib/mysql
 - cache/:/tmp/cache
 - ~/configs:/etc/configs/:ro
```

如果路径为数据卷名称，必须在文件中配置数据卷。

```yaml
version: "3"

services:
  my_src:
    image: mysql:8.0
    volumes:
      - mysql_data:/var/lib/mysql

volumes:
  mysql_data:  
```

### 其它指令
此外，还有包括 domainname, entrypoint, hostname, ipc, mac_address, privileged, read_only, shm_size, restart, stdin_open, tty, user, working_dir 等指令，基本跟 docker run 中对应参数的功能一致。

指定服务容器启动后执行的入口文件。

```yaml
entrypoint: /code/entrypoint.sh
```
指定容器中运行应用的用户名。

```yaml
user: nginx
```
指定容器中工作目录。

```yaml
working_dir: /code
```

指定容器中搜索域名、主机名、mac 地址等。

```yaml
domainname: your_website.com
hostname: test
mac_address: 08-00-27-00-0C-0A
```

允许容器中运行一些特权命令。

```yaml
privileged: true
```
指定容器退出后的重启策略为始终重启。该命令对保持服务始终运行十分有效，在生产环境中推荐配置为 always 或者 unless-stopped。

```yaml
restart: always
```
以只读模式挂载容器的 root 文件系统，意味着不能对容器内容进行修改。

```yaml
read_only: true
```
打开标准输入，可以接受外部输入。

```yaml
stdin_open: true
```
模拟一个伪终端。

```yaml
tty: true
```
### 读取变量
Compose 模板文件支持动态读取主机的系统环境变量和当前目录下的 .env 文件中的变量。

例如，下面的 Compose 文件将从运行它的环境中读取变量 ${MONGO_VERSION} 的值，并写入执行的指令中。

```yaml 
version: "3"
services:

db:
  image: "mongo:${MONGO_VERSION}"
```
如果执行 MONGO_VERSION=3.2 docker-compose up 则会启动一个 mongo:3.2 镜像的容器；如果执行 MONGO_VERSION=2.8 docker-compose up 则会启动一个 mongo:2.8 镜像的容器。

若当前目录存在 .env 文件，执行 docker-compose 命令时将从该文件中读取变量。

在当前目录新建 .env 文件并写入以下内容。
```yaml
# 支持 # 号注释
MONGO_VERSION=3.6
```

执行 docker-compose up 则会启动一个 mongo:3.6 镜像的容器。

---

## Docker Compose 例子


服务 (service)：一个应用容器，实际上可以运行多个相同镜像的实例。

项目 (project)：由一组关联的应用容器组成的一个完整业务单元。

可见，一个项目可以由多个服务（容器）关联而成，Compose 面向项目进行管理。

### 场景
最常见的项目是 web 网站，该项目应该包含 web 应用和缓存。

下面我们用 Python 来建立一个能够记录页面访问次数的 web 网站。

#### web 应用
新建文件夹，在该目录中编写 app.py 文件

```python
from flask import Flask
from redis import Redis

app = Flask(__name__)
redis = Redis(host='redis', port=6379)

@app.route('/')
def hello():
    count = redis.incr('hits')
    return 'Hello World! 该页面已被访问 {} 次。\n'.format(count)

if __name__ == "__main__":
    app.run(host="0.0.0.0", debug=True)
```

#### Dockerfile
编写 Dockerfile 文件，内容为

```bash
FROM python:3.6-alpine
ADD . /code
WORKDIR /code
RUN pip install redis flask
CMD ["python", "app.py"]
docker-compose.yml
```
编写 docker-compose.yml 文件，这个是 Compose 使用的主模板文件。

```yaml
version: '3'
services:

  web:
    build: .
    ports:
     - "5000:5000"

  redis:
    image: "redis:alpine"
```

#### 运行 compose 项目

```bash
$ docker-compose up
```

此时访问本地 5000 端口，每次刷新页面，计数就会加 1。

---

## Docker Compose 命令说明

### 命令对象与格式
对于 Compose 来说，大部分命令的对象既可以是项目本身，也可以指定为项目中的服务或者容器。如果没有特别的说明，命令对象将是项目，这意味着项目中所有的服务都会受到命令影响。

执行 docker-compose [COMMAND] --help 或者 docker-compose help [COMMAND] 可以查看具体某个命令的使用格式。

docker-compose 命令的基本的使用格式是

```bash
docker-compose [-f=<arg>...] [options] [COMMAND] [ARGS...]
```

### 命令选项
-f, --file FILE 指定使用的 Compose 模板文件，默认为 docker-compose.yml，可以多次指定。

-p, --project-name NAME 指定项目名称，默认将使用所在目录名称作为项目名。

--verbose 输出更多调试信息。

-v, --version 打印版本并退出。

### 命令使用说明

build

格式为 docker-compose build [options] [SERVICE...]。

构建（重新构建）项目中的服务容器。

服务容器一旦构建后，将会带上一个标记名，例如对于 web 项目中的一个 db 容器，可能是 web_db。

可以随时在项目目录下运行 docker-compose build 来重新构建服务。

选项包括：

> --force-rm 删除构建过程中的临时容器。
> --no-cache 构建镜像过程中不使用 cache（这将加长构建过程）。
> --pull 始终尝试通过 pull 来获取更新版本的镜像。

config
验证 Compose 文件格式是否正确，若正确则显示配置，若格式错误显示错误原因。

down
此命令将会停止 up 命令所启动的容器，并移除网络

exec
进入指定的容器。

help
获得一个命令的帮助。

images
列出 Compose 文件中包含的镜像。

kill
格式为 docker-compose kill [options] [SERVICE...]。

通过发送 SIGKILL 信号来强制停止服务容器。

支持通过 -s 参数来指定发送的信号，例如通过如下指令发送 SIGINT 信号。

```bash
$ docker-compose kill -s SIGINT
```
logs
格式为 docker-compose logs [options] [SERVICE...]。

查看服务容器的输出。默认情况下，docker-compose 将对不同的服务输出使用不同的颜色来区分。可以通过 --no-color 来关闭颜色。

该命令在调试问题的时候十分有用。

pause
格式为 docker-compose pause [SERVICE...]。

暂停一个服务容器。

port
格式为 docker-compose port [options] SERVICE PRIVATE_PORT。

打印某个容器端口所映射的公共端口。

选项：

--protocol=proto 指定端口协议，tcp（默认值）或者 udp。

--index=index 如果同一服务存在多个容器，指定命令对象容器的序号（默认为 1）。

ps
格式为 docker-compose ps [options] [SERVICE...]。

列出项目中目前的所有容器。

选项：

-q 只打印容器的 ID 信息。

pull
格式为 docker-compose pull [options] [SERVICE...]。

拉取服务依赖的镜像。

选项：

--ignore-pull-failures 忽略拉取镜像过程中的错误。

push
推送服务依赖的镜像到 Docker 镜像仓库。

restart
格式为 docker-compose restart [options] [SERVICE...]。

重启项目中的服务。

选项：

-t, --timeout TIMEOUT 指定重启前停止容器的超时（默认为 10 秒）。

rm
格式为 docker-compose rm [options] [SERVICE...]。

删除所有（停止状态的）服务容器。推荐先执行 docker-compose stop 命令来停止容器。

选项：

-f, --force 强制直接删除，包括非停止状态的容器。一般尽量不要使用该选项。

-v 删除容器所挂载的数据卷。

run
格式为 docker-compose run [options] [-p PORT...] [-e KEY=VAL...] SERVICE [COMMAND] [ARGS...]。

在指定服务上执行一个命令。

例如：

```bash
$ docker-compose run ubuntu ping docker.com
```

将会启动一个 ubuntu 服务容器，并执行 ping docker.com 命令。

默认情况下，如果存在关联，则所有关联的服务将会自动被启动，除非这些服务已经在运行中。

该命令类似启动容器后运行指定的命令，相关卷、链接等等都将会按照配置自动创建。

两个不同点：

给定命令将会覆盖原有的自动运行命令；

不会自动创建端口，以避免冲突。

如果不希望自动启动关联的容器，可以使用 --no-deps 选项，例如

```bash
$ docker-compose run --no-deps web python manage.py shell
```

将不会启动 web 容器所关联的其它容器。

选项：

-d 后台运行容器。

--name NAME 为容器指定一个名字。

--entrypoint CMD 覆盖默认的容器启动指令。

-e KEY=VAL 设置环境变量值，可多次使用选项来设置多个环境变量。

-u, --user="" 指定运行容器的用户名或者 uid。

--no-deps 不自动启动关联的服务容器。

--rm 运行命令后自动删除容器，d 模式下将忽略。

-p, --publish=[] 映射容器端口到本地主机。

--service-ports 配置服务端口并映射到本地主机。

-T 不分配伪 tty，意味着依赖 tty 的指令将无法运行。

scale
格式为 docker-compose scale [options] [SERVICE=NUM...]。

设置指定服务运行的容器个数。

通过 service=num 的参数来设置数量。例如：

```bash
$ docker-compose scale web=3 db=2
```

将启动 3 个容器运行 web 服务，2 个容器运行 db 服务。

一般的，当指定数目多于该服务当前实际运行容器，将新创建并启动容器；反之，将停止容器。

选项：

-t, --timeout TIMEOUT 停止容器时候的超时（默认为 10 秒）。

start
格式为 docker-compose start [SERVICE...]。

启动已经存在的服务容器。

stop
格式为 docker-compose stop [options] [SERVICE...]。

停止已经处于运行状态的容器，但不删除它。通过 docker-compose start 可以再次启动这些容器。

选项：

-t, --timeout TIMEOUT 停止容器时候的超时（默认为 10 秒）。

top
查看各个服务容器内运行的进程。

unpause
格式为 docker-compose unpause [SERVICE...]。

恢复处于暂停状态中的服务。

up
格式为 docker-compose up [options] [SERVICE...]。

该命令十分强大，它将尝试自动完成包括构建镜像，（重新）创建服务，启动服务，并关联服务相关容器的一系列操作。

链接的服务都将会被自动启动，除非已经处于运行状态。

可以说，大部分时候都可以直接通过该命令来启动一个项目。

默认情况，docker-compose up 启动的容器都在前台，控制台将会同时打印所有容器的输出信息，可以很方便进行调试。

当通过 Ctrl-C 停止命令时，所有容器将会停止。

如果使用 docker-compose up -d，将会在后台启动并运行所有的容器。一般推荐生产环境下使用该选项。

默认情况，如果服务容器已经存在，docker-compose up 将会尝试停止容器，然后重新创建（保持使用 volumes-from 挂载的卷），以保证新启动的服务匹配 docker-compose.yml 文件的最新内容。如果用户不希望容器被停止并重新创建，可以使用 docker-compose up --no-recreate。这样将只会启动处于停止状态的容器，而忽略已经运行的服务。如果用户只想重新部署某个服务，可以使用 docker-compose up --no-deps -d <SERVICE_NAME> 来重新创建服务并后台停止旧服务，启动新服务，并不会影响到其所依赖的服务。

选项：

-d 在后台运行服务容器。

--no-color 不使用颜色来区分不同的服务的控制台输出。

--no-deps 不启动服务所链接的容器。

--force-recreate 强制重新创建容器，不能与 --no-recreate 同时使用。

--no-recreate 如果容器已经存在了，则不重新创建，不能与 --force-recreate 同时使用。

--no-build 不自动构建缺失的服务镜像。

-t, --timeout TIMEOUT 停止容器时候的超时（默认为 10 秒）。

version
格式为 docker-compose version。

打印版本信息。

---





## Docker Compose 使用

使用 docker-compose 配置Laravel项目

项目结构：

```bash
laravel-docker-project/
├── docker-compose.yml # Docker 服务编排配置
├── Dockerfile # PHP 镜像构建配置
├── nginx/
│ └── default.conf # Nginx 服务器配置
├── .env # 环境变量配置
├── .gitignore # Git 忽略规则
└── README.md # 项目说明文档
```



Dockerfile 配置：

```bash
# Dockerfile

# 使用 PHP 8.4 FPM Alpine 镜像
FROM php:8.4-fpm-alpine

# 安装系统依赖和 PHP 扩展
RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev \
    zip \
    unzip \
    oniguruma-dev

# 配置并安装 PHP 扩展
RUN docker-php-ext-configure gd --with-freetype --with-jpeg
RUN docker-php-ext-install \
    pdo \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip

# 安装 Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 设置工作目录
WORKDIR /var/www/html

# 复制 composer 文件并安装依赖（利用 Docker 缓存层）
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader

# 复制项目文件
COPY . .

# 设置存储目录权限
RUN chown -R www-data:www-data /var/www/html/storage
RUN chown -R www-data:www-data /var/www/html/bootstrap/cache
RUN chmod -R 775 /var/www/html/storage
RUN chmod -R 775 /var/www/html/bootstrap/cache

# 创建非 root 用户
RUN addgroup -g 1000 -S www && adduser -u 1000 -S www -G www
USER www

# 暴露端口
EXPOSE 9000

# 启动 PHP-FPM
CMD ["php-fpm"]

```

nginx/default.conf 配置：

```bash
# 定义虚拟主机
server {
    # 监听 80 端口（HTTP）
    listen 80;
    listen [::]:80;
    # 服务器名称（可以使用通配符或具体域名）
    server_name localhost;

    # 网站根目录（Laravel 的 public 目录）
    root /var/www/html/public;

    # 默认索引文件
    index index.php index.html index.htm;

    # 字符集设置
    charset utf-8;

    # 主要请求处理
    location / {
        # 尝试按顺序查找文件：
        # 1. 直接访问的文件（如 CSS、JS）
        # 2. 目录索引
        # 3. 如果都不存在，交给 Laravel 的路由处理
        try_files $uri $uri/ /index.php?$query_string;
    }

    # 处理 favicon.ico（避免日志中记录 404）
    location = /favicon.ico {
        access_log off;
        log_not_found off;
    }

    # 处理 robots.txt
    location = /robots.txt  {
        access_log off;
        log_not_found off;
    }

    # 404 错误页面处理（交给 Laravel）
    error_page 404 /index.php;

    # PHP 文件处理配置
    location ~ \.php$ {
        # 将请求转发给 PHP-FPM 服务
        # "app" 是 docker-compose.yml 中定义的服务名
        # 9000 是 PHP-FPM 默认端口
        fastcgi_pass app:9000;

        # 默认索引文件
        fastcgi_index index.php;

        # 设置 PHP 脚本文件路径
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;

        # 包含 FastCGI 标准参数
        include fastcgi_params;

        # 超时设置（避免长时间处理超时）
        fastcgi_read_timeout 300;
        fastcgi_connect_timeout 300;
    }

    # 禁止访问隐藏文件（.开头的文件，除了 .well-known）
    location ~ /\.(?!well-known).* {
        deny all;
    }
}

```

docker-compose.yml 配置：

```bash
# 定义 Docker Compose 版本
version: "3.8"

# 定义服务（容器）
services:
    # PHP 应用服务 - 运行 Laravel 代码
    app:
        # 构建自定义镜像（因为我们需要特定的 PHP 环境）
        build:
            context: . # 使用当前目录作为构建上下文
            dockerfile: Dockerfile # 指定 Dockerfile 文件
        # 给构建的镜像命名，便于管理
        image: laravel-app:php8.4
        # 容器名称
        container_name: laravel_app
        # 自动重启策略（除非手动停止，否则总是重启）
        restart: unless-stopped
        # 工作目录
        working_dir: /var/www/html
        # 数据卷挂载（将主机文件同步到容器）
        volumes:
            # 将当前项目目录挂载到容器的 /var/www/html
            # 这样在主机修改代码，容器内立即生效（开发环境非常有用）
            - .:/var/www/html
        # 网络配置 - 所有服务在同一个网络内，可以通过服务名互相访问
        networks:
            - laravel_network
        # 依赖关系 - 确保 mysql 服务先启动
        depends_on:
            - mysql
        # 环境变量（也可以从 .env 文件读取）
        environment:
            - APP_ENV=local
            - DB_HOST=mysql # 使用服务名作为主机名
            - DB_DATABASE=laravel
            - DB_USERNAME=laravel
            - DB_PASSWORD=secret

    # Nginx Web 服务器 - 处理 HTTP 请求
    webserver:
        # 使用官方 Nginx 镜像（无需自定义构建）
        image: nginx:alpine
        container_name: laravel_nginx
        restart: unless-stopped
        # 端口映射：主机端口:容器端口
        # 访问 http://localhost:8000 会转发到容器的 80 端口
        ports:
            - "8000:80"
        # 数据卷挂载
        volumes:
            # 挂载代码（与 app 服务相同的挂载点）
            - .:/var/www/html
            # 挂载 Nginx 配置文件（覆盖默认配置）
            - ./nginx/default.conf:/etc/nginx/conf.d/default.conf
        networks:
            - laravel_network
        # 依赖关系 - 确保 PHP 服务先启动
        depends_on:
            - app

    # MySQL 数据库服务
    mysql:
        image: mysql:8.0
        container_name: laravel_mysql
        restart: unless-stopped
        # 数据库配置（通过环境变量）
        environment:
            MYSQL_ROOT_PASSWORD: secret # root 用户密码
            MYSQL_DATABASE: laravel # 创建的数据库名
            MYSQL_USER: laravel # 创建的用户名
            MYSQL_PASSWORD: secret # 用户密码
            MYSQL_CHARSET: utf8mb4 # 字符集
            MYSQL_COLLATION: utf8mb4_unicode_ci # 排序规则
        # 数据卷挂载 - 使用命名卷持久化数据库数据
        volumes:
            # mysql_data 是在下面 volumes 部分定义的命名卷
            # 这样即使删除容器，数据库数据也不会丢失
            - mysql_data:/var/lib/mysql
        networks:
            - laravel_network
        # 可选：将数据库端口映射到主机，方便用工具连接
        ports:
            - "3306:3306"

# 定义数据卷（顶级配置）
volumes:
    # 命名卷：mysql_data
    # 用于持久化 MySQL 数据，独立于容器生命周期
    mysql_data:
        driver: local # 使用本地驱动

# 定义网络（顶级配置）
networks:
    # 自定义网络：laravel_network
    # 所有服务加入这个网络后，可以通过服务名互相访问
    # 比如 app 服务可以通过 "mysql" 主机名访问数据库
    laravel_network:
        driver: bridge # 使用桥接网络

```


启动项目：

```bash
docker-compose up -d
```
