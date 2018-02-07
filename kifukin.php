<?php
// 変数の初期化
$page_flag = 0;
$clean = array(); 
$error = array();

//サニタイズ
if( !empty($_POST) ) {
    foreach( $_POST as $key => $value ) {
        $clean[$key] = htmlspecialchars( $value, ENT_QUOTES);
    }
}

if( !empty($clean['btn_confirm']) ) {
    $error = validation($clean);
    if( empty($error) ) {
	    $page_flag = 1;
	    
	    // セッションの書き込み
	    session_start();
	    $_SESSION['page'] = true;
    }
} elseif( !empty($clean['btn_submit']) ) {
    session_start();
    if( !empty($_SESSION['page']) && $_SESSION['page'] === true ) {
        
     // セッションの削除
     unset($_SESSION['page']);
	$page_flag = 2;
	
	// 変数とタイムゾーンを初期化
	$header = null;
	$auto_reply_subject = null;
	$auto_reply_text = null;
	$admin_reply_subject = null;
	$admin_reply_text = null;
	date_default_timezone_set('Asia/Tokyo');
	
	//日本語の使用宣言
	mb_language("ja");
	mb_internal_encoding("UTF-8");
	
	// メールのヘッダー情報を設定
	$header = "MIME-Version: 1.0\n";
	$header .= "From: 寄付の受付 <noreply@gray-code.com>\n";
	$header .= "Reply-To: 寄付の受付 <noreply@gray-code.com>\n";
	
	// メールの件名を設定
	$auto_reply_subject = '寄付のお申し込みをありがとうございます。';
	
	// 本文を設定
	$auto_reply_text = "この度は、寄付のお申し込みを頂き誠にありがとうございます。下記の内容でお申し込みを受け付けました。\n\n";
	$auto_reply_text .= "お申し込み日時：" . date("Y-m-d H:i") . "\n";
	$auto_reply_text .= "氏名：" . $_POST['namesei'] .$_POST['namemei']."様". "\n";
	$auto_reply_text .= "メールアドレス：" . $_POST['mail_1'] . "\n\n";
	$auto_reply_text .= "寄付の受付サイト";
	
	// メール送信
	mb_send_mail( $_POST['mail_1'], $auto_reply_subject, $auto_reply_text);
	
	// 運営側へ送るメールの件名
	$admin_reply_subject = "寄付のお申し込みがありました";
	
	// 本文を設定
	$admin_reply_text = "下記の内容でお申し込みがありました。\n\n";
	$admin_reply_text .= "お申し込み日時：" . date("Y-m-d H:i") . "\n";
	$admin_reply_text .= "氏名：" . $_POST['namesei'] .$_POST['namemei']. "\n";
	$admin_reply_text .= "メールアドレス：" . $_POST['mail_1'] . "\n\n";
	
	// 運営側へメール送信
	mb_send_mail( 'a-ishibashi@jia.co.jp', 
	$admin_reply_subject, $admin_reply_text, $header);
    } else {
        $page_flag = 0;
    }
}

function validation($data) {
    $error = array();
    
    // 寄付金額のバリデーション
    if( $data['type'] == '一回' &&  $data['donate_type_one'] !== '1,000円' &&  $data['donate_type_one'] !== '5,000円' &&  $data['donate_type_one'] !== '10,000円') {
        $error[] = "「寄付金額」をご選択ください";
    }
    if( $data['type'] == 'マンスリー' &&  $data['donate_type_month'] !== '毎月1,000円' &&  $data['donate_type_month'] !== '毎月5,000円' &&  $data['donate_type_month'] !== '毎月10,000円') {
        $error[] = "「寄付金額」をご選択ください";
    }
    // 領収書のバリデーション
    if(empty($data['receipt']) ) {
        $error[] = "「領収書の発行」をご選択ください";
    }elseif($data['receipt'] !== '希望する' && $data['receipt'] !== '希望しない'  ) {
        $error[] = "「領収書の発行」をご選択ください";
    }
    // 個人/法人のバリデーション
    if( empty($data['corpo']) ) {
        $error[] = "「個人/法人・団体」をご選択ください";
    }
    // 姓のバリデーション
    if( empty($data['namesei']) ) {
        $error[] = "「姓」をご入力ください";
    }
    // 名のバリデーション
    if( empty($data['namemei']) ) {
        $error[] = "「名」をご入力ください";
    }
    // せいのバリデーション
    if( empty($data['furinamesei']) ) {
        $error[] = "「せい」をご入力ください";
    }
    // めいのバリデーション
    if( empty($data['furinamemei']) ) {
        $error[] = "「めい」をご入力ください";
    }
    // 性別のバリデーション
    if( empty($data['gender']) ) {
        $error[] = "「性別」をご選択ください";
    }elseif( $data['gender'] !== '男' && $data['gender'] !== '女' ) {
        $error[] = "「性別」をご選択ください";
    }
    // 生年のバリデーション
    if( empty($data['birth']) ) {
        $error[] = "「生年」をご選択ください";
    }
    // 郵便番号のバリデーション
    if( empty($data['postnumber_1']) ) {
        $error[] = "「郵便番号の左3桁」をご入力ください";
    }
    // 郵便番号のバリデーション
    if( empty($data['postnumber_2']) ) {
        $error[] = "「郵便番号の右4桁」をご入力ください";
    }
    // 都道府県のバリデーション
    if( empty($data['adress_1']) ) {
        $error[] = "「都道府県」をご選択ください";
    }
    // 市町区村のバリデーション
    if( empty($data['adress_2']) ) {
        $error[] = "「市町区村」をご入力ください";
    }
    // 番地のバリデーション
    if( empty($data['adress_3']) ) {
        $error[] = "「番地」をご入力ください";
    }
    // 電話番号のバリデーション
    if( empty($data['phone']) ) {
        $error[] = "「電話番号」をご入力ください";
    }
    // メールアドレスのバリデーション
    if( empty($data['mail_1']) ) {
        $error[] = "「メールアドレス」をご入力ください";
    }else if( !preg_match( '/^[0-9a-z_.\/?-]+@([0-9a-z-]+\.)+[0-9a-z-]+$/', $data['mail_1']) ) {
        $error[] = "「メールアドレス」は正しい形式で入力してください。";
    }
    // 確認メールアドレスのバリデーション
    if( empty($data['mail_2']) ) {
        $error[] = "「確認用のメールアドレス」をご入力ください";
    }elseif( $data['mail_2'] !== $data['mail_1'] ) {
        $error[] = "確認用のメールアドレスが異なります";
    }

    return $error;
}
?>





<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="robots" content="noindex">
    <title>寄付金の受付</title>

<!--最低限のcss-->    
<style>
   li{list-style-type:none;}
   table{border: 1px #000000 solid; border-collapse: collapse;}
   td {border: 1px #2b2b2b solid; padding: 10px; width:600px;}
   th {border: 1px #2b2b2b solid; padding: 10px; width:100px;}
   ul{-webkit-padding-start: 5px; -webkit-padding-end: 10px;}
</style>
<!--最低限のcssここまで--> 
</head>


<body>
 <!--ページタイトル-->
 <h2>寄付金の受付</h2>


<!--確認ページ領域-->
    <?php if( $page_flag === 1 ): ?>
    
    <form action="" method="POST" name="check">  
    <!--寄付金の種類と金額-->  
    <h3>寄付の種類と金額をお選びください</h3>
        <table>
            <tbody>
                <tr>
                    <th>
                        <p>寄付の種類</p>
                    </th>
                    <td>
                        <ul>
                            <li>
                                 <?php echo $_POST['type']; ?>
                            </li> 
                       </ul>
                    </td>   
               </tr>        
                <tr>
                    <th>
                        <p>寄付額</p>
                    </th>
                    <td>
                        <ul>
                            <li>
                                <?php 
                                   $type = $_POST['type'];
                                   if($type == 一回){
                                        echo $_POST['donate_type_one'];
                                    }else if($type == マンスリー){
                                        echo $_POST['donate_type_month']; 
                                    }
                                ?>
                            </li>
                        </ul>
                    </td>
                </tr> 
            </tbody>
        </table>
        
    <!--お支払い方法--> 
    <h3>お支払い方法をお選びください</h3>    
        <table>
            <tbody>
                <tr>
                    <th>
                        <p>お支払い方法</p>
                    </th>
                    <td>
                        <ul>
                            <li>
                                <?php echo $_POST['payment']; ?>
                            </li>
                        </ul>
                    </td>
                </tr>
                <tr>
                    <th>
                        領収書の発行
                    </th>
                    <td>
                        <ul>
                            <li>
                                <?php echo $_POST['receipt']; ?>
                            </li>
                        </ul>
                    </td>
                </tr>
            </tbody>
        </table>
        
    <!--個人情報の入力--> 
   <h3>個人情報の入力</h3>   
        <table>
            <tbody>
                <tr>
                    <th>
                        <p>個人/法人</p>
                    </th>
                    <td>
                        <ul>
                            <li>
                                <?php echo $_POST['corpo']; ?>
                            </li>
                        </ul>
                    </td>
                </tr>
                <tr>
                    <th>
                        <p>お名前</p>
                    </th>
                    <td>
                        <ul>
                            <li>
                                <?php echo $_POST['namesei']; echo $_POST['namemei']; ?>
                            </li>
                        </ul>
                    </td>
                </tr>
                <tr>
                    <th>
                        <p>ふりがな</p>
                    </th>
                    <td>
                        <ul>
                            <li>
                                <?php echo $_POST['furinamesei']; echo $_POST['furinamemei']; ?>
                            </li>
                        </ul>
                    </td>
                </tr>
                <tr>
                    <th>
                        <p>性別</p>
                    </th>
                    <td>
                        <ul>
                            <li>
                                <?php echo $_POST['gender']; ?>
                            </li>
                        </ul>
                    </td>
                </tr>
                <tr>
                    <th>
                        <p>生年</p>
                    </th>
                    <td>
                        <ul>
                            <li>
                                <?php echo $_POST['birth']; ?>
                            </li>
                        </ul>
                    </td>
                </tr>
                <tr>
                    <th>
                        <p>郵便番号</p>
                    </th>
                    <td>
                        <ul>
                            <li>
                                <?php echo $_POST['postnumber_1']; ?>ー<?php echo $_POST['postnumber_2']; ?>
                            </li>
                        </ul>
                    </td>
                </tr>
                <tr>
                    <th>
                        <p>住所</p>
                    </th>
                    <td>
                        <ul>
                            <li>
                                都道府県：<?php echo $_POST['adress_1']; ?><br />
                                市町区村：<?php echo $_POST['adress_2']; ?><br />
                                番地：<?php echo $_POST['adress_3']; ?><br />
                                建物等：<?php echo $_POST['adress_4']; ?>
                            </li>
                        </ul>
                    </td>
                </tr>

                <tr>
                    <th>
                        <p>電話番号</p>
                    </th>
                    <td>
                        <ul>
                            <li>
                                <?php echo $_POST['phone']; ?>
                            </li>
                        </ul>
                    </td>
                </tr>
                <tr>
                    <th>
                        <p>メールアドレス</p>
                    </th>
                    <td>
                        <ul>
                            <li>
                                <?php echo $_POST['mail_1']; ?>
                            </li>
                        </ul>
                    </td>
                </tr>
            </tbody>
        </table> 
        <input type="submit" name="btn_back" value="戻る">
        <input type="submit" name="btn_submit" value="送信">
        <input type="hidden" name="type" value="<?php echo $_POST['type']; ?>" >
        <input type="hidden" name="donate_type_one" value="<?php echo $_POST['donate_type_one']; ?>">
        <input type="hidden" name="donate_type_month" value="<?php echo $_POST['donate_type_month']; ?>">
        <input type="hidden" name="payment" value="<?php echo $_POST['payment']; ?>">
        <input type="hidden" name="receipt" value="<?php echo $_POST['receipt']; ?>">
        <input type="hidden" name="corpo" value="<?php echo $_POST['corpo']; ?>">
        <input type="hidden" name="namesei" value="<?php echo $_POST['namesei']; ?>">
        <input type="hidden" name="namemei" value="<?php echo $_POST['namemei']; ?>">
        <input type="hidden" name="furinamesei" value="<?php echo $_POST['furinamesei']; ?>">
        <input type="hidden" name="furinamemei" value="<?php echo $_POST['furinamemei']; ?>">
        <input type="hidden" name="gender" value="<?php echo $_POST['gender']; ?>">
        <input type="hidden" name="birth" value="<?php echo $_POST['birth']; ?>">
        <input type="hidden" name="postnumber_1" value="<?php echo $_POST['postnumber_1']; ?>">
        <input type="hidden" name="postnumber_2" value="<?php echo $_POST['postnumber_2']; ?>">
        <input type="hidden" name="adress_1" value="<?php echo $_POST['adress_1']; ?>">
        <input type="hidden" name="adress_2" value="<?php echo $_POST['adress_2']; ?>">
        <input type="hidden" name="adress_3" value="<?php echo $_POST['adress_3']; ?>">
        <input type="hidden" name="adress_4" value="<?php echo $_POST['adress_4']; ?>">
        <input type="hidden" name="phone" value="<?php echo $_POST['phone']; ?>">
        <input type="hidden" name="mail_1" value="<?php echo $_POST['mail_1']; ?>">
        <input type="hidden" name="mail_2" value="<?php echo $_POST['mail_2']; ?>">
    </form>
    <?php elseif( $page_flag === 2 ): ?>
    <p>送信が完了しました。</p>
　　<input type="button" name="button" value="TOPページへ" onClick="location.href='http://google.com'">
    <?php else: ?>







 <!--入力フォーム領域-->
 <?php if( !empty($error) ): ?>
     <ul style="color: #ff2e5a;text-align: left; padding: 10px 30px; font-size: 86%; border: 1px solid #ff2e5a; border-radius: 5px;list-style-type:disc;">
         <?php foreach( $error as $value ): ?>
             <li style="list-style-type:disc;"><?php echo $value; ?></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>


    <form action="" method="POST"> 
     <!--寄付金の種類と金額-->  
    <h3>寄付の種類と金額をお選びください</h3>


        <table>
            <tbody>
                <tr>
                    <th>
                        <p>寄付の種類</p>
                    </th>
                    <td>
                        <ul>
                            <li>
                                <input type="radio" name="type" id="type_one" value="一回" <?php if( !empty($_POST['type']) && $_POST['type'] === "一回" ){ echo 'checked'; } ?>  onclick="entryChange1();" checked="checked"/>
                                一回<br />
                                <span style="font-size: 0.8em;">任意の金額をその都度、寄付いただく方法です<br /><br /></span>
                            </li> 
                            <li>
                                <input type="radio" name="type" id="type_month" value="マンスリー" <?php if( !empty($_POST['type']) && $_POST['type'] === "マンスリー" ){ echo 'checked'; } ?>  onclick="entryChange1();"> 
                                マンスリー<br />
                                <span style="font-size: 0.8em;">継続的に毎月、寄付いただく方法です。<br />お支払いはカードのみとなります。</span>
                            </li>
                       </ul>
                    </td>   
               </tr> 
               <!-- 表示非表示切り替え -->
                <tr id="firstBox">
                    <th>
                        <p>寄付額</p>
                    </th>
                    <td>
                        <ul>
                            <li>
                                <select name="donate_type_one" id="donate_type_one" value="">
                                    <option>選択してください</option>
                                    <option label="1,000円" value="1,000円" <?php if( !empty($_POST['donate_type_one']) && $_POST['donate_type_one'] === "1,000円" ){ echo 'selected'; } ?>>1,000円</option>
                                    <option label="5,000円" value="5,000円" <?php if( !empty($_POST['donate_type_one']) && $_POST['donate_type_one'] === "5,000円" ){ echo 'selected'; } ?>>5,000円</option>
                                    <option label="10,000円" value="10,000円" <?php if( !empty($_POST['donate_type_one']) && $_POST['donate_type_one'] === "10,000円" ){ echo 'selected'; } ?>>10,000円</option>
                                </select>
                            </li>
                        </ul>
                    </td>
                </tr> 
                <!-- 表示非表示切り替え -->
　　　　　　　　<tr id="secondBox">
                    <th>
                        <p>寄付額</p>
                    </th>
                    <td>
                        <ul>
                            <li>
                                <select name="donate_type_month" id="donate_type_month" value="">
                                    <option value>選択してください</option>
                                    <option label="毎月1,000円" value="毎月1,000円" <?php if( !empty($_POST['donate_type_month']) && $_POST['donate_type_month'] === "毎月1,000円" ){ echo 'selected'; } ?>>毎月1,000円</option>
                                    <option label="毎月5,000円" value="毎月5,000円" <?php if( !empty($_POST['donate_type_month']) && $_POST['donate_type_month'] === "毎月5,000円" ){ echo 'selected'; } ?>>毎月5,000円</option>
                                    <option label="毎月10,000円" value="毎月10,000円" <?php if( !empty($_POST['donate_type_month']) && $_POST['donate_type_month'] === "毎月10,000円" ){ echo 'selected'; } ?>>毎月10,000円</option>
                                </select>
                            </li>
                        </ul>
                    </td>
                </tr>
            </tbody>
        </table>
 
    <!--お支払い方法--> 
    <h3>お支払い方法をお選びください</h3>    
        <table>
            <tbody>
                <tr>
                    <th>
                        <p>お支払い方法</p>
                    </th>
                    <td>
                        <ul>
                            <li id="paym_bank" onclick="entryChange2();" >
                                
                                <input type="radio" name="payment" id="payment_bank" value="口座振込"  <?php if( !empty($_POST['payment']) && $_POST['payment'] === "口座振込" ){ echo 'checked'; } ?> checked="checked"　/>
                                口座振込<br />
                                <!-- 表示非表示切り替え -->
                                <div id="ABox" style="background-color:#eeeeee;font-size: 0.8em;padding: 5px;margin-bottom:10px;">
                                    <ul>
                                        <li>
                                            <p>振込口座＊＊＊＊＊＊</p>
                                            <p style="text-indent: 2em;">＊＊銀行＊＊支店01234567</p>
                                        </li>
                                        <li>
                                            <p>お支払いの流れ</p>
                                        </li>
                                        <li>
                                            <p style="text-indent: 2em;">申し込みフォーム送信</p>
                                            <p style="text-indent: 2em;">↓</p>
                                            <p style="text-indent: 2em;">ご登録のメールアドレスに「受付完了メール」をお送りします。</p>
                                            <p style="text-indent: 2em;">↓</p>
                                            <p style="text-indent: 2em;">金融機関からお振込手続きをお願いします。</p>
                                            <p style="text-indent: 2em;">↓</p>
                                            <p style="text-indent: 2em;">お振込確認後、領収書をお送りいたします。</p>
                                        </li>
                                    </ul>
                                </div>
                              
                            </li>
                            <li id="paym_credit" onclick="entryChange2();" >
                                <input type="radio" name="payment" id="payment_credit" value="クレジットカード" <?php if( !empty($_POST['payment']) && $_POST['payment'] === "クレジットカード" ){ echo 'checked'; } ?>  > 
                                クレジットカード<br />
                                <span style="font-size: 0.8em; color:red;">＊マンスリーのお支払いはカードのみとなります</span>
                                <!-- 表示非表示切り替え -->
                                <div id="BBox" style="background-color:#eeeeee;font-size: 0.8em;padding: 5px;"> 
                                    <ul>
                                        <li>
                                            <p>ご利用いただけるカード会社</p>
                                            <p style="text-indent: 2em;">Visa,Amex,JCB</p>
                                        </li>
                                        <li>
                                            <p>お支払いの流れ</p>
                                        </li>
                                        <li>
                                            <p style="text-indent: 2em;">申し込みフォーム送信</p>
                                            <p style="text-indent: 2em;">↓</p>
                                            <p style="text-indent: 2em;">カード決済画面に移行後決済のお手続き</p>
                                            <p style="text-indent: 2em;">↓</p>
                                            <p style="text-indent: 2em;">ご登録のメールアドレスに「受付完了メール」をお送りします。</p>
                                            <p style="text-indent: 2em;">↓</p>
                                            <p style="text-indent: 2em;">ご請求の翌月にクレジットカード会社から1more Baby応援団へ入金されます。</p>
                                            <p style="text-indent: 2em;">↓</p>
                                            <p style="text-indent: 2em;">1月～12月の入金分（受領分）をまとめた「年間領収書」を翌年1月下旬までにお送りします。</p>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                        </ul>
                    </td>
                </tr>
                <tr>
                    <th>
                        領収書の発行
                    </th>
                    <td>
                        <ul>
                            <li>
                                <lavel>
                                    <input type="radio" id="receipt1" name="receipt" <?php if( !empty($_POST['receipt']) && $_POST['receipt'] === "希望する" ){ echo 'checked'; } ?> value="希望する">希望する
                                </lavel>
                            </li>
                            <li>
                                <lavel>
                                    <input type="radio" id="receipt2" name="receipt" <?php if( !empty($_POST['receipt']) && $_POST['receipt'] === "希望しない" ){ echo 'checked'; } ?> value="希望しない">希望しない
                                </lavel>
                            </li>
                        </ul>
                    </td>
                </tr>
            </tbody>
        </table>
        
    <!--個人情報の入力--> 
    <h3>個人情報の入力</h3>   
        <table>
            <tbody>
                <tr>
                    <th>
                        <p>個人/法人</p>
                    </th>
                    <td>
                        <ul>
                            <li>
                                <lavel>
                                    <input type="radio" id="corpo_nocorporate" name="corpo" value="個人" <?php if( !empty($_POST['corpo']) && $_POST['corpo'] === "個人" ){ echo 'checked'; } ?>>個人
                                </lavel>
                            </li>
                            <li>
                                <lavel>
                                    <input type="radio" id="corpo_corporate" name="corpo" <?php if( !empty($_POST['corpo']) && $_POST['corpo'] === "法人・団体" ){ echo 'checked'; } ?> value="法人・団体">法人・団体
                                </lavel>
                            </li>
                        </ul>
                    </td>
                </tr>
                <tr>
                    <th>
                        <p>お名前</p>
                    </th>
                    <td>
                        <ul>
                            <li>
                                <label>
                                    <span>姓</span>
                                    <input type="text" name="namesei" maxlength="20" id="namesei" value="<?php if( !empty($_POST['namesei']) ){ echo $_POST['namesei']; } ?>"  value style="ime-mode: active;"/>
                                </label>
                            </li>
                            <li>
                                <label>
                                    <span>名</span>
                                    <input type="text" name="namemei" maxlength="20" id="namemei" value="<?php if( !empty($_POST['namemei']) ){ echo $_POST['namemei']; } ?>" value style="ime-mode: active;"/>
                                </label>
                            </li>
                        </ul>
                    </td>
                </tr>
                <tr>
                    <th>
                        <p>ふりがな</p>
                    </th>
                    <td>
                        <ul>
                            <li>
                                <label>
                                    <span>せい</span>
                                    <input type="text" name="furinamesei" maxlength="20" id="furinamesei" value="<?php if( !empty($_POST['furinamesei']) ){ echo $_POST['furinamesei']; } ?>" value style="ime-mode: active;"/>
                                </label>
                            </li>
                            <li>
                                <label>
                                    <span>めい</span>
                                    <input type="text" name="furinamemei" maxlength="20" id="furinamemei" value="<?php if( !empty($_POST['furinamemei']) ){ echo $_POST['furinamemei']; } ?>" value style="ime-mode: active;"/>
                                </label>
                            </li>
                        </ul>
                    </td>
                </tr>
                <tr>
                    <th>
                        <p>性別</p>
                    </th>
                    <td>
                        <ul>
                            <li>
                                <lavel>
                                    <input type="radio" id="female" name="gender" value="男" <?php if( !empty($_POST['gender']) && $_POST['gender'] === "男" ){ echo 'checked'; } ?>>男
                                </lavel>
                            </li>
                            <li>
                                <lavel>
                                    <input type="radio" id="male" name="gender" value="女" <?php if( !empty($_POST['gender']) && $_POST['gender'] === "女" ){ echo 'checked'; } ?>>女
                                </lavel>
                            </li>
                        </ul>
                    </td>
                </tr>
                <tr>
                    <th>
                        <p>生年</p>
                    </th>
                    <td>
                        <ul>
                            <li>
                                 <select name="birth" id="birth" value="">
                                    <option value>選択してください</option>
                                    <?php $now = date("Y");
                                    for($i = 1911; $i<= $now; $i++):?>
                                    <option value="<?php echo $i;?>" <?php if( !empty($_POST['birth']) && $_POST['birth'] === "$i" ){ echo 'selected'; } ?>><?php echo $i;?></option>
                                    <?php endfor;?>
                                </select>
                            </li>
                        </ul>
                    </td>
                </tr>
                <tr>
                    <th>
                        <p>郵便番号</p>
                    </th>
                    <td>
                        <ul>
                            <li>
                                <label>
                                    <input type="text" name="postnumber_1" maxlength="3" id="postnumber_1" value="<?php if( !empty($_POST['postnumber_1']) ){ echo $_POST['postnumber_1']; } ?>" value style="ime-mode: disabled;"/>
                                </label>
                                -
                                <label>
                                    <input type="text" name="postnumber_2" maxlength="4" id="postnumber_2" value="<?php if( !empty($_POST['postnumber_2']) ){ echo $_POST['postnumber_2']; } ?>" value style="ime-mode: disabled;"/>
                                </label>
                            </li>
                        </ul>
                    </td>
                </tr>
                <tr>
                    <th>
                        <p>住所</p>
                    </th>
                    <td>
                        <ul>
                            <li>
                                都道府県
                            </li>
                            <li>
                                <select name="adress_1" id="adress_1" value="">
                                    <option value>選択してください</option>
                                    <option value="北海道" <?php if( !empty($_POST['adress_1']) && $_POST['adress_1'] === "北海道" ){ echo 'selected'; } ?>>北海道</option>
                                    <option value="青森県" <?php if( !empty($_POST['adress_1']) && $_POST['adress_1'] === "青森県" ){ echo 'selected'; } ?>>青森県</option>
                                    <option value="岩手県" <?php if( !empty($_POST['adress_1']) && $_POST['adress_1'] === "岩手県" ){ echo 'selected'; } ?>>岩手県</option>
                                    <option value="宮城県" <?php if( !empty($_POST['adress_1']) && $_POST['adress_1'] === "宮城県" ){ echo 'selected'; } ?>>宮城県</option>
                                    <option value="秋田県" <?php if( !empty($_POST['adress_1']) && $_POST['adress_1'] === "秋田県" ){ echo 'selected'; } ?>>秋田県</option>
                                    <option value="山形県" <?php if( !empty($_POST['adress_1']) && $_POST['adress_1'] === "山形県" ){ echo 'selected'; } ?>>山形県</option>
                                    <option value="福島県" <?php if( !empty($_POST['adress_1']) && $_POST['adress_1'] === "福島県" ){ echo 'selected'; } ?>>福島県</option>
                                    <option value="茨城県" <?php if( !empty($_POST['adress_1']) && $_POST['adress_1'] === "茨城県" ){ echo 'selected'; } ?>>茨城県</option>
                                    <option value="栃木県" <?php if( !empty($_POST['adress_1']) && $_POST['adress_1'] === "栃木県" ){ echo 'selected'; } ?>>栃木県</option>
                                    <option value="群馬県" <?php if( !empty($_POST['adress_1']) && $_POST['adress_1'] === "群馬県" ){ echo 'selected'; } ?>>群馬県</option>
                                    <option value="埼玉県" <?php if( !empty($_POST['adress_1']) && $_POST['adress_1'] === "埼玉県" ){ echo 'selected'; } ?>>埼玉県</option>
                                    <option value="千葉県" <?php if( !empty($_POST['adress_1']) && $_POST['adress_1'] === "千葉県" ){ echo 'selected'; } ?>>千葉県</option>
                                    <option value="東京都" <?php if( !empty($_POST['adress_1']) && $_POST['adress_1'] === "東京都" ){ echo 'selected'; } ?>>東京都</option>
                                    <option value="神奈川県" <?php if( !empty($_POST['adress_1']) && $_POST['adress_1'] === "神奈川県" ){ echo 'selected'; } ?>>神奈川県</option>
                                    <option value="新潟県" <?php if( !empty($_POST['adress_1']) && $_POST['adress_1'] === "新潟県" ){ echo 'selected'; } ?>>新潟県</option>
                                    <option value="富山県" <?php if( !empty($_POST['adress_1']) && $_POST['adress_1'] === "富山県" ){ echo 'selected'; } ?>>富山県</option>
                                    <option value="石川県" <?php if( !empty($_POST['adress_1']) && $_POST['adress_1'] === "石川県" ){ echo 'selected'; } ?>>石川県</option>
                                    <option value="福井県" <?php if( !empty($_POST['adress_1']) && $_POST['adress_1'] === "福井県" ){ echo 'selected'; } ?>>福井県</option>
                                    <option value="山梨県" <?php if( !empty($_POST['adress_1']) && $_POST['adress_1'] === "山梨県" ){ echo 'selected'; } ?>>山梨県</option>
                                    <option value="長野県" <?php if( !empty($_POST['adress_1']) && $_POST['adress_1'] === "長野県" ){ echo 'selected'; } ?>>長野県</option>
                                    <option value="岐阜県" <?php if( !empty($_POST['adress_1']) && $_POST['adress_1'] === "岐阜県" ){ echo 'selected'; } ?>>岐阜県</option>
                                    <option value="静岡県" <?php if( !empty($_POST['adress_1']) && $_POST['adress_1'] === "静岡県" ){ echo 'selected'; } ?>>静岡県</option>
                                    <option value="愛知県" <?php if( !empty($_POST['adress_1']) && $_POST['adress_1'] === "愛知県" ){ echo 'selected'; } ?>>愛知県</option>
                                    <option value="三重県" <?php if( !empty($_POST['adress_1']) && $_POST['adress_1'] === "三重県" ){ echo 'selected'; } ?>>三重県</option>
                                    <option value="滋賀県" <?php if( !empty($_POST['adress_1']) && $_POST['adress_1'] === "滋賀県" ){ echo 'selected'; } ?>>滋賀県</option>
                                    <option value="京都府" <?php if( !empty($_POST['adress_1']) && $_POST['adress_1'] === "京都府" ){ echo 'selected'; } ?>>京都府</option>
                                    <option value="大阪府" <?php if( !empty($_POST['adress_1']) && $_POST['adress_1'] === "大阪府" ){ echo 'selected'; } ?>>大阪府</option>
                                    <option value="兵庫県" <?php if( !empty($_POST['adress_1']) && $_POST['adress_1'] === "兵庫県" ){ echo 'selected'; } ?>>兵庫県</option>
                                    <option value="奈良県" <?php if( !empty($_POST['adress_1']) && $_POST['adress_1'] === "奈良県" ){ echo 'selected'; } ?>>奈良県</option>
                                    <option value="和歌山県" <?php if( !empty($_POST['adress_1']) && $_POST['adress_1'] === "和歌山県" ){ echo 'selected'; } ?>>和歌山県</option>
                                    <option value="鳥取県" <?php if( !empty($_POST['adress_1']) && $_POST['adress_1'] === "鳥取県" ){ echo 'selected'; } ?>>鳥取県</option>
                                    <option value="島根県" <?php if( !empty($_POST['adress_1']) && $_POST['adress_1'] === "島根県" ){ echo 'selected'; } ?>>島根県</option>
                                    <option value="岡山県" <?php if( !empty($_POST['adress_1']) && $_POST['adress_1'] === "岡山県" ){ echo 'selected'; } ?>>岡山県</option>
                                    <option value="広島県" <?php if( !empty($_POST['adress_1']) && $_POST['adress_1'] === "広島県" ){ echo 'selected'; } ?>>広島県</option>
                                    <option value="山口県" <?php if( !empty($_POST['adress_1']) && $_POST['adress_1'] === "山口県" ){ echo 'selected'; } ?>>山口県</option>
                                    <option value="徳島県" <?php if( !empty($_POST['adress_1']) && $_POST['adress_1'] === "徳島県" ){ echo 'selected'; } ?>>徳島県</option>
                                    <option value="香川県" <?php if( !empty($_POST['adress_1']) && $_POST['adress_1'] === "香川県" ){ echo 'selected'; } ?>>香川県</option>
                                    <option value="愛媛県" <?php if( !empty($_POST['adress_1']) && $_POST['adress_1'] === "愛知県" ){ echo 'selected'; } ?>>愛媛県</option>
                                    <option value="高知県" <?php if( !empty($_POST['adress_1']) && $_POST['adress_1'] === "高知県" ){ echo 'selected'; } ?>>高知県</option>
                                    <option value="福岡県" <?php if( !empty($_POST['adress_1']) && $_POST['adress_1'] === "福岡県" ){ echo 'selected'; } ?>>福岡県</option>
                                    <option value="佐賀県" <?php if( !empty($_POST['adress_1']) && $_POST['adress_1'] === "佐賀県" ){ echo 'selected'; } ?>>佐賀県</option>
                                    <option value="長崎県" <?php if( !empty($_POST['adress_1']) && $_POST['adress_1'] === "長崎県" ){ echo 'selected'; } ?>>長崎県</option>
                                    <option value="熊本県" <?php if( !empty($_POST['adress_1']) && $_POST['adress_1'] === "熊本県" ){ echo 'selected'; } ?>>熊本県</option>
                                    <option value="大分県" <?php if( !empty($_POST['adress_1']) && $_POST['adress_1'] === "大分県" ){ echo 'selected'; } ?>>大分県</option>
                                    <option value="宮崎県" <?php if( !empty($_POST['adress_1']) && $_POST['adress_1'] === "宮崎県" ){ echo 'selected'; } ?>>宮崎県</option>
                                    <option value="鹿児島県" <?php if( !empty($_POST['adress_1']) && $_POST['adress_1'] === "鹿児島県" ){ echo 'selected'; } ?>>鹿児島県</option>
                                    <option value="沖縄県" <?php if( !empty($_POST['adress_1']) && $_POST['adress_1'] === "沖縄県" ){ echo 'selected'; } ?>>沖縄県</option>

                                </select>
                            </li>
                            <li>
                                市長区村<br />例：千代田区飯田橋
                            </li>
                            <li>
                                <label>
                                    <input type="text" name="adress_2" maxlength="22" id="adress_2" value="<?php if( !empty($_POST['adress_2']) ){ echo $_POST['adress_2']; } ?>" value style="ime-mode: active;"/>
                                </label>
                            </li>  
                            <li>
                                番地<br />
                                例：１ー２ー３（ハイフン"ー"で区切ってください）
                            </li>
                            <li>
                                <label>
                                    <input type="text" name="adress_3" maxlength="22" id="adress_3" value="<?php if( !empty($_POST['adress_3']) ){ echo $_POST['adress_3']; } ?>" value style="ime-mode: active;"/>
                                </label>
                            </li> 
                            <li>
                                建物等（任意）<br />
                                例：AAAビル１０１号室
                            </li>
                            <li>
                                <label>
                                    <input type="text" name="adress_4" maxlength="22" id="adress_4" value="<?php if( !empty($_POST['adress_4']) ){ echo $_POST['adress_4']; } ?>" value style="ime-mode: active;"/>
                                </label>
                            </li> 
                            </li>
                        </ul>
                    </td>
                </tr>
                <tr>
                    <th>
                        <p>電話番号</p>
                    </th>
                    <td>
                        <ul>
                            <li>
                                例：0312345678 市外局番から入力してください
                            </li>
                            <li>
                                <label>
                                   <input type="text" name="phone" maxlength="13" id="phone" value="<?php if( !empty($_POST['phone']) ){ echo $_POST['phone']; } ?>" style="ime-mode: disabled";/>
                                </label>
                            </li>
                        </ul>
                    </td>
                </tr>
                <tr>
                    <th>
                        <p>メールアドレス</p>
                    </th>
                    <td>
                        <ul>
                            <li>
                                寄付完了後、確認のメールをお送りします
                            </li>
                            <li>
                                <label>
                                    <input type="text" name="mail_1" maxlength="50" id="mail_1" value="<?php if( !empty($_POST['mail_1']) ){ echo $_POST['mail_1']; } ?>" style="ime-mode: disabled";/>
                                </label>
                            </li>
                            <li>
                                確認のためにもう一度入力してください
                            </li>
                            <li>
                                <label>
                                    <input type="text" name="mail_2" maxlength="50" id="mail_2" value="<?php if( !empty($_POST['mail_2']) ){ echo $_POST['mail_2']; } ?>" style="ime-mode: disabled;"/>
                                </label>
                            </li>
                        </ul>
                    </td>
                </tr>
            </tbody>
        </table>
        
           <input type="submit" name="btn_confirm" value="入力内容を確認する">
    </form>
    <?php endif; ?>

                        <p>メールアドレス</p>

<!--ここからjavascript--> 
<script type="text/javascript">
//寄付額の表示非常時とクレジットカード選択不可
    function entryChange1(){
    radio = document.getElementsByName('type') 
    if(radio[0].checked) { //一回が選択されている時
        document.getElementById('firstBox').style.display = "";　//寄付額：一回用を表示
        document.getElementById('secondBox').style.display = "none";　//寄付額：マンスリー用を非表示
        document.getElementById('paym_bank').style.display = "";　//支払い方法：振込を表示
        document.getElementById('paym_credit').style.display = "";　//支払い方法：クレジットを表示
    }else if(radio[1].checked) {//マンスリーを選択した時
        document.getElementById('firstBox').style.display = "none";　//寄付額：一回用を非表示
        document.getElementById('secondBox').style.display = "";　//寄付額：マンスリー用を表示
        document.getElementById('paym_bank').style.display = "none";　//支払い方法：振込を非表示
        document.getElementById('paym_credit').style.display = "";　//支払い方法：クレジットを表示
    }
}
//お支払いの流れの表示非表示
    function entryChange2(){
    radio = document.getElementsByName('payment') 
    if(radio[0].checked) {　//支払い方法：振込を選択した時
        document.getElementById('BBox').style.display = "none"; //支払いの流れ：クレジットを非表示
        document.getElementById('ABox').style.display = ""; //支払いの流れ：振込を表示
    }else if(radio[1].checked) {　//支払い方法：クレジットを選択した時
        document.getElementById('ABox').style.display = "none";　 //支払いの流れ：振込を非表示
        document.getElementById('BBox').style.display = "";　//支払いの流れ：クレジットを表示
    }
}
//確認ページ送信先の変更
/*
    var Form = document.getElementById("check");
    if( $data['type'] == 'マンスリー'){
        //判定式がTrueの場合、送信先は***.htmlになる
        paymentForm.action="https://www.yahoo.co.jp";
    }
*/
//オンロードさせ、リロード時に選択を保持
window.addEventListener('load', entryChange2() )
window.addEventListener('load', entryChange1() )


</script>

</body>
</html>