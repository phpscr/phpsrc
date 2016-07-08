PHP命名规范   .



.
:

参考文章（11）   
.

使用PHP写的框架必然有其自身的一定规范，在 ThinkPHP  中亦然。下面是使用 ThinkPHP  应该尽量遵循的命名规范： 

²  类文件都是以  .class.php 为后缀（这里是指的  ThinkPHP 内部使用的类库文件，不代表外部加载的类库文件），使用驼峰法命名，并且首字母大写，例如 DbMysql.class.php  。 

²  函数、配置文件等其他类库文件之外的一般是以 .php 为后缀（第三方引入的不做要求）。 

²  确保文件的命名和调用大小写一致，是由于在类 Unix 系统上面，对大小写是敏感的（而 ThinkPHP  在调试模式下面，即使在 Windows  平台也会严格检查大小写）。 

²  类名和文件名一致（包括上面说的大小写一致），例如 UserAction  类的文件命名是 UserAction.class.php  ， InfoModel  类的文件名是 InfoModel.class.php  ， 

²  函数的命名使用小写字母和下划线的方式，例如 get_client_ip 

²  Action  控制器类以 Action  为后缀，例如 UserAction  、 InfoAction 

²  模型类以  Model 为后缀，例如  UserModel 、  InfoModel 

²  方法的命名使用驼峰法，并且首字母小写，例如 getUserName 

²  属性的命名使用驼峰法，并且首字母小写，例如 tableName 

²  以双下划线“  __ ”打头的函数或方法作为魔法方法，例如 __call 和 __autoload 

²  常量以大写字母和下划线命名，例如 HAS_ONE 和 MANY_TO_MANY 

²  配置参数以大写字母和下划线命名，例如 HTML_CACHE_ON 

²  语言变量以大写字母和下划线命名，例如 MY_LANG ，以下划线打头的语言变量通常用于系统语言变量，例如 _CLASS_NOT_EXIST_  。 

²  数据表和字段采用小写加下划线方式命名，例如 think_user 和 user_name 

特例： 

在 ThinkPHP  里面，有一个函数命名的特例，就是单字母大写函数，这类函数通常是某些操作的快捷定义，或者有特殊的作用。例如，  ADSL 方法等等，他们有着特殊的含义，后面会有所了解。 

另外一点， ThinkPHP  默认使用 UTF-8  编码，所以请确保你的程序文件采用 UTF-8  编码格式保存，并且去掉 BOM  信息头（去掉 BOM  头信息有很多方式，不同的编辑器都有设置方法，也可以用工具进行统一检测和处理）。 

