<?php 
include "mup_out.php";
?>
<!DOCTYPE HTML>
<html>
<head>
<meta charset="utf-8">
<title>MUP Test</title>
<?php
$master = new MUMaster();
$myCSS = new MUCSS();
$myCSS->AddProperty("div","border","1px solid black");	
$myCSS->AddProperty("div","max-width","33%");
$myCSS->AddProperty("div","padding","3px");
$myCSS->AddProperty(".test1","border","1px dotted red");
$myCSS->AddProperty("#test2","border","none");
$myCSS->AddProperty("#test2","border-bottom","1px solid blue");
$myCSS->AddProperty("#test2","font-weight","bold");
$myCSS->AddProperty("div","color","orange");
$myCSS->GetCSS(true,false);
?>
</head>
<body>
<?php
$div1 = new MUDiv();
$div2 = new MUDiv();
$div3 = new MUDiv();
echo $master->nonce("span","This is a nonce span.","bob","people");
$header1 = new MUHdr(2);
$header1("This is my header.");
$div2->addclass("test1");
$div3->addclass("test1");
$div3->addid("test2");
echo $master->comment("The result of appending to div1 using __invoke is " . $div1("This is the first div."));;
$div1($master->noncelink("This is a nonce link.", "#blorb"));
$div2->append("This is the second div, called 'test1'.");
$div3->append("This is the third div, called 'test2'.");
echo $master->comment("Using toString to get div1's contents:");
echo $div1;
$header1->output(true,false);
$div2->output(true,true);
$link1 = new MULink();
$link1->addhref("#");
$link1->addtarget("_blank");
$link1->append("This is a link.");
$link1->prepend("This is prepended.");
$div4 = new MUDiv();
$span1 = new MUSpan();
$span1->append("This is a span in the fourth div.");
$div4->append($span1->output(false,false));
$div3->appendbr(1);
$div3->append($link1->output(false,true));
$div3->append($div4->output(false,false));
$div3->prepend("This is the last thing added to the div.");
$div3->output(true,true);
$header1->output(true,true);
$div1->appendbr(1);
$div1->append("Div1's got more in it now.");
$div1->output(true,true);
?>
</body>
</html>