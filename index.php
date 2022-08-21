<?php

namespace Index;

use Error;

$human = array();

ini_set('log_errors','on');  //ログを取るか
ini_set('error_log','php.log');  //ログの出力ファイルを指定
session_start(); //セッション使う

//固定値
//ランダムでお笑いネタを出す回数
define('FUNPOINT','3');


//属性定義
class Attribute {
    const KI = 1;
    const DO = 2;
    const AI = 3;
    const RAKU = 4;
    const SAKE = 5;
    const CUTE = 6;
    const WISE = 7;
    const HENTAI = 8;
}

//敵および味方のカード総数
class CardNum {
    const TEKI_CARD_SUM = 1;
    const MIKATA_CARD_SUM = 8;  //変更の際はjqueryも変更すること！
}

//抽象クラス(生き物クラス)
abstract class Creature{
    protected $name;
    protected $img;
    protected $attribute;
    protected $exp;

    protected $point;
    protected $temp;

    abstract public function saySerihu();

    //get関数
    public function getName() {
        return $this->name;
    }
    public function getImg() {
        return $this->img;
    }
    public function getAttribute() {
        switch($this->attribute) {
            case Attribute::KI:
                $temp = '喜';
                break;
            case Attribute::DO:
                $temp = '怒';
                break;
            case Attribute::AI:
                $temp = '哀';
                break;
            case Attribute::RAKU:
                $temp = '楽';
                break;
            case Attribute::SAKE:
                $temp = '酒';
                break;
            case Attribute::CUTE:
                $temp = '美';
                break;
            case Attribute::WISE:
                $temp = '賢';
                break;
            case Attribute::HENTAI:
                $temp = '変';
                break;
        }
        return $temp;
    }
    public function getExp() {
        return $this->exp;
    }

    //set関数
    public function setExp($num) {
        $this->exp = $num;
    }

    public function CalcExp($targetObj) {
        switch ($this->getAttribute()) {
            case Attribute::KI:
                $point = 10;
                break;
            case Attribute::DO:
                $point = 15;
                break;
            case Attribute::AI:
                $point = 20;
                break;
            case Attribute::RAKU:
                $point = 25;
            case Attribute::SAKE:
                $point = 5;
                break;
            case Attribute::CUTE:
                $point = 45;
                break;
            case Attribute::WISE:
                $point = 38;
                break;
            case Attribute::HENTAI:
                $point = 3;
                break;
        }
        //経験値の更新
        $targetObj->setExp($targetObj->getExp() + $point);
        //経験値獲得のセリフ
        Process::set($this->getName().'は、'.$this->getExp().'の経験値を得た！');
        //使った回数のカウント
        $_SESSION['cntWin'] += 1;
        error_log('勝った回数' . $_SESSION['cntWin']);
        if($_SESSION['cntWin'] > CardNum::MIKATA_CARD_SUM){
            //ゲームクリアのセリフ
            Process::set('<br>'.$this->getName().'は無事にゲームクリア！！');
            Process::set('続けてゲームをおこなう場合は右上のリセットボタンを押してください');
        } else {
            error_log('ゲーム継続');
        }
    }
}

class NormalHuman extends Creature {
    public function __construct($name, $img, $attribute, $exp)
    {
        $this->name = $name;
        $this->img = $img;
        $this->attribute = $attribute;
        $this->exp = $exp;
    }
    //決めセリフ
    public function saySerihu() {
        Process::set('どうだやったぞ！');
    }
}

class FunHuman extends Creature {
    //プロパティ
    private $funPoint;

    //コンストラクタ
    public function __construct($name, $img, $attribute, $exp, $funPoint) {
        $this->name = $name;
        $this->img = $img;
        $this->attribute = $attribute;
        $this->exp = $exp;
        $this->funPoint = $funPoint;
    }

    public function saySerihu()
    {
        Process::set('やったぞぐへへ、、、');
        Process::set($this->name.'はうれしすぎてよだれがでた。。。');
    }

    public function CalcExp($targetObj) {
        switch ($this->attribute) {
            case Attribute::KI:
                $point = 10;
                break;
            case Attribute::DO:
                $point = 15;
                break;
            case Attribute::AI:
                $point = 20;
                break;
            case Attribute::RAKU:
                $point = 25;
            case Attribute::SAKE:
                $point = 5;
                break;
            case Attribute::CUTE:
                $point = 45;
                break;
            case Attribute::WISE:
                $point = 38;
                break;
            case Attribute::HENTAI:
                $point = 3;
                break;
        }
        //経験値の更新
        if (!mt_rand(0,3) && $this->funPoint > 0) {
            //ハイテンションになり経験値アップ
            Process::set('<br>属性が一致したので'.$targetObj->getName().'はハイテンションだ！');
            Process::set('経験値が多くもらえるぞ！');
            $point +=30;
            $this->funPoint += 1;
        }
        $targetObj->setExp($targetObj->getExp() + $point);
        //経験値獲得のセリフ
        Process::set('<br>'.$this->getName().'は'.$this->getExp().'の経験値を得た！');
    }
}

interface ProcessInterface{
    public static function set($str);
    public static function clr();
    public static function sanitize($str);
}

class Process implements ProcessInterface{
    //変数
    protected $temp;

    //進行欄更新用
    public static function set($str) {
        //セッションがなければ作成する
        if (empty($_SESSION['process'])) $_SESSION['process'] = '';
        //進行欄更新
        $_SESSION['process'] .= $str.'<br>';
    }
    //進行欄クリア
    public static function clr(){
        //進行欄クリア
        unset($_SESSION['process']);
    }
    //サニタイズ
    public static function sanitize($str){
        return htmlspecialchars($str);
    }
}

//カード内の値算出用
interface CardInterface {
    public static function setUpNum();
    public static function setAttribute();
    public static function setDownNum();
    public static function ArrangeCard($kinds);
    public static function judgeCard($teki, $mikata);
}

class CalcCardNum implements CardInterface {

    

    //カード内上の値の算出
    public static function setUpNum()
    {
        return(mt_rand(1,9));
    }
    //カード真ん中の属性の文字の算出
    public static function setAttribute()
    {
        $temp = mt_rand(Attribute::KI, Attribute::HENTAI);

        switch($temp) {
            case Attribute::KI:
                $temp = '喜';
                break;
            case Attribute::DO:
                $temp = '怒';
                break;
            case Attribute::AI:
                $temp = '哀';
                break;
            case Attribute::RAKU:
                $temp = '楽';
                break;
            case Attribute::RAKU:
                $temp = '酒';
                break;
            case Attribute::SAKE:
                $temp = '美';
                break;
            case Attribute::CUTE:
                $temp = '賢';
                break;
            case Attribute::WISE:
                $temp = '変';
                break;
        }
        return $temp;
    }
    //カード下の数字を漢字に変換
    public static function setDownNum()
    {
        $temp = mt_rand(1,9);

        switch($temp) {
            case 1:
                $temp = '一';
                break;
            case 2:
                $temp = '二';
                break;
            case 3:
                $temp = '三';
                break;
            case 4:
                $temp = '四';
                break;
            case 5:
                $temp = '五';
                break;
            case 6:
                $temp = '六';
                break;
            case 7:
                $temp = '七';
                break;
            case 8:
                $temp = '八';
                break;
            case 9:
                $temp = '九';
                break;
        }
        return $temp;
    }

    //味方カードの算出
    public static function ArrangeCard($kinds) {
        $temp = array();

        for($i=0; $i<$kinds; $i++) {
            $temp[$i]['up'] = CalcCardNum::setUpNum();
            $temp[$i]['middle'] = CalcCardNum::setAttribute();
            $temp[$i]['down'] = CalcCardNum::setDownNum();
        }
        return $temp;
    }
    //ひとつひとつの勝負の勝敗をつける
    public static function judgeCard($teki, $mikata){
        //どのカードが選択されたかを判定
        for ($i=0; $i<CardNum::MIKATA_CARD_SUM; $i++){
            if(!empty($_POST['attack'.$i])){
                $temp = $i;

                //値がみつかったのでループ終了
                $i = CardNum::MIKATA_CARD_SUM;
            }
        }

        error_log('対戦中　$敵カード' . print_r($teki, true));
        error_log('対戦中　$味方カード' . print_r($mikata[$temp], true));

        Process::set('敵の出したカード：' . $teki[0]['up']);
        Process::set('あなたの出したカード：' . $mikata[$temp]['up']);

        //勝敗判定
        //備考：9は1に負けるという特集ルールを採用
        if($teki[0]['up'] < $mikata[$temp]['up'] ||
           $teki[0]['up'] === 9 && $mikata[$temp]['up'] === 1 ) {
            return true;
        } elseif($teki[0]['up'] === 1 && $mikata[$temp]['up'] === 9){
            return false;
        } else {
            return false;
        }

    }
}

//----------
//インスタンス
//----------
$human[] = new NormalHuman('太郎', 'img/blackman1_smile.png', Attribute::KI, 0);
$human[] = new NormalHuman('泣助', 'img/blackman1_cry.png', Attribute::AI, 0);
$human[] = new NormalHuman('史郎', 'img/blackman1_laugh.png', Attribute::RAKU, 0);
$human[] = new NormalHuman('きれい子', 'img/otaku_girl_fashion.png', Attribute::CUTE, 0);
$human[] = new NormalHuman('出来杉', 'img/apron_man2-1idea.png', Attribute::WISE, 0);
$human[] = new FunHuman('変態野郎', 'img/Shiningcolor.png', Attribute::HENTAI, 0, FUNPOINT);
$human[] = new FunHuman('次郎', 'img/blackman1_angry.png', Attribute::DO, 0, FUNPOINT);
$human[] = new FunHuman('酒夫', 'img/yopparai_businessman.png', Attribute::SAKE, 0, FUNPOINT);

//----------
//事前処理
//----------
error_log('$_SESSIONの値1：'. print_r($_SESSION,true));
error_log('$_POSTの値1：'. print_r($_POST,true));

//ゲーム開始したか
$startFlg = (!empty($_POST['game-start']) && empty($_SESSION['human']))? true : false;
//途中でゲームをやめたか
$resetFlg = (!empty($_POST['reset']))? true : false;

//ゲーム開始後、じぶんのカードを選んだか
if( !empty($_POST['attack0']) ||
    !empty($_POST['attack1']) ||
    !empty($_POST['attack2']) ||
    !empty($_POST['attack3']) ||
    !empty($_POST['attack4']) ||
    !empty($_POST['attack5']) ||
    !empty($_POST['attack6']) ||
    !empty($_POST['attack7'])){
    $fightFlg = true;
    //リロード用：リロードするとtrueになるのでここでfalseにしておく
    $startFlg = false;
} else {
    $fightFlg = false;
}

//----------
//メイン処理
//----------

//ポスト情報取得
if(!empty($_POST)) {

    error_log('POSTされた！');



    error_log('$startFlg:'.$startFlg);
    error_log('$fightFlg:'.$fightFlg);
/*
    var_dump('$resetFlg:'.$resetFlg);
    var_dump('$startFlg:'.$startFlg);
    var_dump('$fightFlg:'.$fightFlg);
*/
    //----------
    //モード管理
    //----------

    //テスト用
    //$startFlg = false;
    
    //途中でゲームをやめたとき
    if($resetFlg){
        $_SESSION = array();
        $_POST = array();
        $startFlg = false;
        $fightFlg = false;
    //ゲームをはじめた直後のとき。カードを選択するまではここにいる
    }elseif($startFlg && !$fightFlg) {
        $_SESSION['game-now'] = 'fight!!';
        error_log('first step');
        $_SESSION['human'] = $human[mt_rand(1,7)];

        //味方カードの算出
        $_SESSION['mikataCard'] = CalcCardNum::ArrangeCard(CardNum::MIKATA_CARD_SUM);
        error_log('味方カード：' . print_r($_SESSION['mikataCard'],true));

        //敵カードの生成
        $_SESSION['tekiCard'] = CalcCardNum::ArrangeCard(CardNum::TEKI_CARD_SUM);
        error_log('敵カード：' . print_r($_SESSION['tekiCard'], true));
        
        Process::set('ゲームをはじめます。');
        Process::set('じぶんの好きなカードをえらんでください。');

        //カード選択後
    } elseif($fightFlg) {
        //これまでの進行メッセージ削除(邪魔なので)
        Process::clr();
        //再度ゲーム開始処理をしないようにセッションクリア
        unset($_SESSION['game-now']);

        error_log('戦い中');
        //勝敗をつける
        $ret = CalcCardNum::judgeCard($_SESSION['tekiCard'], $_SESSION['mikataCard']);
        
        if($ret) {
            //経験値の計算とゲーム継続可否判定
            $_SESSION['human']->CalcExp($_SESSION['human']);

        } else {
            error_log('負け');
            $_SESSION = array();
            $_POST = array();
            $startFlg = false;
            $fightFlg = false;
        }
    } else {
        error_log('else else else');
    }

    




    //$_SESSION = array();

    error_log('$_SESSIONの値2：'. print_r($_SESSION,true));
    error_log('$_POSTの値2：'. print_r($_POST,true));
    //error_log('$fightFlg:' .$fightFlg );
}



$_POST = array();
error_log('$_POSTの値3：'. print_r($_POST,true));

?>



<head>
    <meta charset="utf-8">
    <title>オブジェクト指向OP</title>
    <link href='http://fonts.googleapis.com/css?family=Montserrat:400,700' rel='stylesheet' type='text/css'>
    <style>
        body {
            width: 100%;
            margin: 0 auto;
        }
        header {
            height: 40px;
            background-color: black;
        }
        header input {
            height: 40px;
            text-align: center;
            float: right;
        }
        header input:hover{
            cursor: pointer;
        }
        ul {
            list-style: none;
            display: inline-block;
            width: 80px;
            border: solid 1px black;
            padding-left: 0;
            text-align: center;
            height: 135px;
            margin: 0;
        }
        li {
            padding-top: 15px;
            padding-bottom: 15px;
            background-color: blueviolet;
           
        }
        li:nth-child(1) {
            background-color: orange;
            background-color: pink;
            border-radius: 55px;
            width: 40px;
            line-height: 10px;
        }
        li:nth-child(2) {
            display: inline-block;
            width: 80px;
        }
        li:nth-child(3) {
            background-color: orange;
            border-radius: 55px;
            width: 40px;
            line-height: 10px;
            float: right;
        }
        li:nth-child(4) {
            /* 4つ目の配列は、どのカードが押されたか用だから非表示 */
            display: none;
        }
         .teki-card {
            text-align: center;
            margin-bottom: 10px;
        }
        .mikata-card {
            text-align: center;
            margin-bottom: 30px;
        }
        .mikata-card ul:hover{
            cursor: pointer;
            border: solid 3px red;
        }
        .story-box {
            border: solid 1px;
            position: absolute;
            height: 200px;
            width: 30%;
            left: 590px;
        }
        .story-box img{
            height: 120px;
            width: 120px;
            position: relative;
            right: 180px;
            top: 10px;
        }
        .story-box figcaption{
            margin-top: 5px;
            position: relative;
            right: 150px;
            top: 10px
        }
        .story-box p {
            display: inline-block;
            position: relative;
            top: -180px;
            left: 10px;
        }
        .start-bottom {
            text-align: center;
            margin-top: 200px;
        }
        .start-bottom label {
            font-size: 100px;
        }
        .start-bottom input {
            font-size: 80px;
            margin-top: 50px;
        }
    </style>
</head>
<body>
    <header>
        <form action="" method="POST">
            <input type="submit" name="reset" value="push-reset">
        </form>
    </header>
    <?php 
    //さいしょのタイトル画面
    if(empty($_SESSION) ) {
        $_SESSION = array();
        error_log('ゲームスタート前');
    ?>
        <div class="start-bottom">
            <form method="post">
                <label>経験値獲得ゲーム！！<br></label>
                <input type="submit" name="game-start" value="GAME START!!">
            </form>
        </div>

    <?php
    //ゲームスタートボタンを推した後
    } else {
    ?>
        <!--敵カードの表示-->
        <div class="teki-card">
            <?php
            foreach($_SESSION['tekiCard'] as $key => $val) {
            ?>
                <ul class="teki-ul<?php echo $key; ?>" data-num = <?php echo $val['up'];?>>
                    <li><?php echo $val['up']; ?></li>
                    <li><?php echo $val['middle']; ?></li>
                    <li><?php echo $val['down']; ?></li>
                </ul>
            <?php
            }
            ?>
        </div>

        <!--味方カードの表示-->
        <div class="mikata-card">
                <?php 
                foreach($_SESSION['mikataCard'] as $key => $val) {
                ?>
                    <ul class="mikata-ul<?php echo $key; ?>" data-num = <?php echo $val['up'];?>>
                        <li><?php echo Process::sanitize($val['up']); ?></li>
                        <li><?php echo Process::sanitize($val['middle']); ?></li>
                        <li><?php echo Process::sanitize($val['down']); ?></li>
                    </ul>
                <?php
                }
                ?>
        </div>

        <!--進行ウィンドウの表示-->
        <div class="story-box">
            <!--味方画像の表示-->
            <img src="<?php echo Process::sanitize($_SESSION['human']->getImg()); ?>"alt="">
            <figcaption><?php echo Process::sanitize($_SESSION['human']->getName()); ?></figcaption>
            <figcaption>属性：<?php echo Process::sanitize($_SESSION['human']->getAttribute()); ?></figcaption>

            <!--進行の文字列の表示-->
            <p><?php if(!empty($_SESSION['process'])) {echo $_SESSION['process']; }; ?></p>
        </div>

        <!-- script -->
        <script src="js/vendor/jquery-2.2.2.min.js"></script>
        <script>
            var $ul0,
                $ul1,
                $ul2,
                $ul3,
                $ul4,
                $ul5,
                $ul6,
                $ul7,
                $ul8;
            var val;

            $(function() {
                $ul0 = $('.mikata-ul0');
                
                $ul0.on('click', function(){
                    val0 = $ul0.find('li').text();
                    //val = $ul1.data('num');
                    console.log(val0);
                    var $this = $(this);
                    $.ajax({
                        type: "POST",
                        url: "index.php",
                        data: {"attack0" : val0}
                    }).done(function(data) {
                        console.log('success');
                        window.location.reload();
                    }).fail(function(msg) {
                        console.log('not!!!!!');
                    });
                });
            });

            $(function() {
                $ul1 = $('.mikata-ul1');

                $ul1.on('click', function() {
                    val1 = $ul1.find('li').text();
                    console.log(val1);
                    var $this = $(this);
                    $.ajax({
                        type: "POST",
                        url: "index.php",
                        data: {"attack1" : val1}
                    }).done(function(data) {
                        console.log('success');
                        window.location.reload();
                    }).fail(function(msg) {
                        console.log('not!!!!!');
                    });
                });
            });

            $(function() {
                $ul2 = $('.mikata-ul2');

                $ul2.on('click', function() {
                    val2 = $ul2.find('li').text();
                    console.log(val2);
                    var $this = $(this);

                    $.ajax({
                        type: "POST",
                        url: "index.php",
                        data: {"attack2" : val2}
                    }).done(function(data) {
                        console.log('sucess');
                        window.location.reload();
                    }).fail(function(msg) {
                        console.log('not!!!!');
                    });
                });
            });

            $(function() {
                $ul3 = $('.mikata-ul3');
                
                $ul3.on('click', function() {
                    val3 = $ul3.find('li').text();
                    console.log(val3);
                    var $this = $(this);
                    $.ajax({
                        type: "POST",
                        url: "index.php",
                        data: { "attack3" : val3}
                    }).done(function(data) {
                        console.log('success');
                        window.location.reload();
                    }).fail(function(msg){
                        console.log('not!!!!');
                    });
                });
            });

            $(function(){
                $ul4 = $('.mikata-ul4');
                
                $ul4.on('click', function(){
                    val4 = $ul4.find('li').text();
                    console.log(val4);
                    $.ajax({
                        type: "POST",
                        url: "index.php",
                        data: { "attack4" : val4}
                    }).done(function(data) {
                        console.log('success');
                        window.location.reload();
                    }).fail(function(msg){
                        console.log('not!!!');
                    });
                });
            });

            $(function(){
                $ul5 = $('.mikata-ul5');

                $ul5.on('click', function(){
                    val5 = $ul5.find('li').text();
                    console.log(val5);
                    $.ajax({
                        type: "POST",
                        url: "index.php",
                        data: { "attack5" : val5}
                    }).done(function(data){
                        console.log('success');
                        window.location.reload();
                    }).fail(function(msg){
                        console.log('not!!!!');
                    });
                });
            });

            $(function(){
                $ul6 = $('.mikata-ul6');
                $ul6.on('click', function(){
                    val6 = $ul6.find('li').text();
                    console.log(val6);
                    $.ajax({
                        type: "POST",
                        url: "index.php",
                        data: { "attack6" : val6}
                    }).done(function(data){
                        console.log('success---');
                        window.location.reload();
                    }).fail(function(msg){
                        console.log('not!!!!');
                    });
                });
            });

            $(function(){
                $ul7 = $('.mikata-ul7');
                $ul7.on('click', function(){
                    val7 = $ul7.find('li').text();
                    console.log(val7);
                    $.ajax({
                        type: "POST",
                        url: "index.php",
                        data: { "attack7" : val7},
                    }).done(function(data){
                        console.log('success');
                        window.location.reload();
                    }).fail(function(msg){
                        console.log('not!!!!');
                    });
                });
            });
        </script>
    <?php
    }
    ?>
</body>