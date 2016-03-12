rare 是一款非常轻量的php框架<br />
[点此下载最新稳定版](http://raremvc.googlecode.com/files/rare-last.zip) 或者从版本库直接获取最新版本。

**麻雀虽小，五脏俱全。**

# rare简介 #
rare是面向对象的。提倡组件化开发模式，让开发程序变得轻松简单。<br />
提供了组件(component)、模板(layout)、过滤器(filter)、路由(router)、类自动加载(class autoload)、静态资源按需加载、框架核心函数钩子(hook)，让代码更容易共用，使用更加方便!

## 组件 ##
组件机制给我们开发高可定制、重用的程序提供了基础。<br />
使用组件，页面就像搭建积木一样简单有趣，用简单的东西可以搭建出丰富有趣的页面。

## 布局 ##
简单明了的模板机制让我们之用关心当前的模块视图，而保证视图文件简单明了，修改布局时只需要修改layout文件即可。<br />
同时透明的ajax支持让我们开发一个有分页的页面的时候不需要写一行代码即支持普通分页和ajax支持（SEO友好）。

## 路由 ##
简单而又强大的路由功能让我们程序的地址看上去更加美观，只需要添加几行配置即可将整个应用的相关地址统一替换。<br />
```
 //路由配置示例
  $config=array();
  //第一种地址模式 id默认匹配\w+
  $config['article/view'][]="article-{id}"; 

  //更加友好的拼音模式
  $config['article/view'][]=array(         
                                  'url'=>"{pinyin}-{id}",
                                  'param'=>array(
                                           'pinyin'=>"\w+",
                                           'id'=>"\d+"
                                          ),
                                  'suffix'=>'jsp' //----url后缀,
                                 );
  return $config;
```
url地址的后缀可以自定义，默认为.html<br />
rare的路由可以轻松的生成和解析类似
```
/beijing 
/shanghai
/wuhan
/\w+    //正则表达式
```
这样多城市的地址而其他模块却依然能正常访问。<br />
rare支持一个模块声明多条路由规则。当您对您的程序的地址调整时，只需要将新的规则置于就的路由规则的前面即可。这样新生成的地址是新的，而用户(以及搜索引擎)依然能正常的访问到之前的地址。<br />
通过rare提供的hook功能，我们可以不用 修改之前的代码而对当前已有的url地址进行统一的调整，比如我们的程序中原始的url传递的参数为cityid=1,但是路由中我们显示的是城市的拼音beijing,有了hook和路由，我们可以将参数进行转换而程序不用做修改(_程序还是统一使用cityid做为参数传递，而不用理会城市的拼音_)，

## 动作 ##
动作支持rest风格<br />
默认的action执行exeute方法，而executePost方法则为post方法请求时执行而不会执行execute。<br />
每个action单独一个文件存储，对应有同名视图文件(view)，团队开发的时候可以明确分工，极大的方便了开发和避免了文件冲突(多人同时编辑一个文件)。<br />
模块化的划分和使之和对应的功能一一对应，即方便了开发，也方便了日后维护，以及SEO友好！<br />

更加全面的伪rest风格:默认不开启<br />
默认配置文件定义
```
$config['customMethod']=true;
```
以使用该功能。
如get 或者post 中有参数method=del,并且 具有executeDel 方法，则该方法会被调用。
控制参数默认为method,也可以是其他的，如配置为
```
$config['customMethod']='rest';
```
则rest=del为有效的调用。

## 过滤器 ##
过滤器机制就是整个程序的一道防火墙，可以在过滤器中进行权限判断，数据预处理等。<br />
支持在所有动作执行前执行 myFilter::beforeExecute 。（这样若在系统内部进行内部模块调整:forward也能进行有效的权限验证等）

```
class myFilter{
  public function doFilter(){
     session_start();
     //若没有登录则跳转到登录页面.
     $context=rareContext::getContext();
     if(!rUser::isLogin() && $context->getActionName(true)!='employee/login'){
         redirect('employee/login');
     }
   }
   
   //每次执行action前执行的方法(rest时有效)
   //若使用了forward了，而且需要做非常严格的权限验证，可以在此处进行权限验证 
   public function beforeExecute(){
     //@todo
   }
}
```
上面就是一个过滤器，非常的简单。<br />
另外，为了使在服务器内部跳转时(forward)也支持过滤器，过滤器也支持每次执行execute方法时执行过滤器。


## 类自动装载 ##
rare内置了类自动装载功能，当使用一个类的使用直接使用而无须require(include) 类文件。<br />
该类自动装载功能非常的独立，若你需要，可以直接在其他框架(任意php程序)中使用。<br />

**1**.先引入 rareAutoLoad.class.php<br />
**2**.注册功能
```
 $option=array(
                'dirs'    => '/www/phplib/',         //class 从那些目录中查找，多个目录使用,分割
                'cache' => '/tmp/111111.php',        //class path 缓存文件
                'suffix'  => '.class.php'            //需要类自动装载的php类文件的后缀
                );
  rareAutoLoad::register($option);
```
为了提高效率，对class信息进行了缓存(类名=>路径)，以保证只会扫描目录一遍。当加载时发现是一个新类名时，会尝试在指定位置重新扫描以加载该类，若类不存在也记录到缓存文件中。<br />
如此，以让加载类和 class\_exists($class\_name,true)的效率达到最优。<br />
文件名必须以英文字母或者数字开头。



## 错误页面 ##
错误页面自定义。<br />
包括404、500错误的错误页面自定义功能。<br />
默认情况下，出现上述错误会是框架默认的一个简单的错误提示页面。<br />
若模块 error/e404、error/e500存在，则其会是app默认的错误页面。

可以在app的默认配置文件(appDir/config/default.php)中定义：
```
//302跳转到其他的页面去
$config['error404']="http://www.example.com/error404.html";
$config['error500']="http://www.example.com/error500.html"; 
```

## 钩子 ##
定制改变、增强框架的默认行为。

## cli支持 ##
直接使用命令行运行程序（运行指定的模块），相当于curl。
编写脚本直接使用当前程序的类库而完成一些计划任务。


框架更多、详细的使用文档请参考 http://rare.hongtao3.com/tutorial.jsp