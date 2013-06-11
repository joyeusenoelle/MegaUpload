<?php

/*
 Plugin Name: Massive Upload
 Plugin URI: http://www.mischievess.com/
 Description: Lets users upload large files directly from the Edit Post page
 Author: Noëlle D. Anthony
 Version: 0.2
 Author URI: http://www.noelleanthony.com/
*/

include "classes/mup_out.php";

/*function __autoload($class_name) {
	include "classes/" . $class_name . ".php";	
}*/


function MUp_validate($input) {
	return $input;	
}

function MUp_addAdminPage() {
	add_options_page('Massive Upload', 'Massive Upload', 'manage_options', 'mup_options', 'MUp_doAdminPage');
}

function MUp_doDF($deffn) {

	$deffile = "";
	if($deffn != "") {
		$cu = wp_get_current_user();
		$cun = $cu->display_name;
		$cun = preg_replace("/ /","",$cun);
		$month = date('F');
		$monnm = date('m');
		$day = date('d');
		$year = date('Y');
		$hour = date('H');
		$minute = date('i');
		$deffile = preg_replace("/\%month\%/",$month,$deffn);
		$deffile = preg_replace("/\%monnm\%/",$monnm,$deffile);
		$deffile = preg_replace("/\%day\%/",$day,$deffile);
		$deffile = preg_replace("/\%year\%/",$year,$deffile);
		$deffile = preg_replace("/\%hour\%/",$hour,$deffile);
		$deffile = preg_replace("/\%minute\%/",$minute,$deffile);
		$deffile = preg_replace("/\%name\%/",$cun,$deffile);
	}
	return $deffile;
	
}

function MUp_upload($post) {
	$options = get_option('mup_options');
	$defdirw = $options['default_directory_which'];
	$defdirn = $options['default_directory'];
	$deffn = $options['default_filename'];
	
	if(intval($defdirw) == 0 || $defdirn == "") {
		$defdir = "";
	} else {
		$defdir = $defdirn;
	}
	
	$goodver = false;
	$curver = explode(".",phpversion());
	if(intval($curver[0]) == 5 && intval($curver[1]) >= 4) {
		$goodver = true;
	}
	?>
    <script type="text/javascript">
		var $muj = jQuery.noConflict();
		var formdata = false;
		var ajaxpath = "<?php echo plugins_url('ajax.php',__FILE__); ?>";
		var progpath = "<?php echo plugins_url('progress.php',__FILE__); ?>";
		var siteurl = "<?php echo site_url(); ?>/";
		var progname = "MU<?php echo mt_rand(5,25); ?>";
		var intvl;
		var inti = 0;
		var intq = 0;
		$muj(document).ready(function(){
			if(window.FormData) {
				formdata = new FormData();
			} else {
				$muj("#mu-postbox-top").html("Your browser does not support features that this plugin needs.");
			}
			document.getElementById('mu-postbox-form-bigfile').addEventListener("change",function(evt) {
				$muj("#mu-postbox-top").html("").hide();
				var n = this.value;
				if(n != "") {
					//alert("n = " + n);
					var n1 = n.split(/(\\|\/)/g).pop();
					//alert("n1 = " + n1);
					var n2 = n1.split(/\./g);
					//alert("n2 = " + n2)
					var nl = n2.length - 1;
					var ne = n2[nl];
					//alert("ne = " + ne)
					var nf = "";
					for(i=0;i<nl;i++) {
						nf = nf + n2[i];
					}
					//alert("nf = " + nf);
					var df = "<?php echo MUp_doDF($deffn); ?>";
					//alert("df = " + df);
					if (df != "") {
						outf = df + "." + ne;
					} else {
						outf = nf + "." + ne;
					}
					//alert("outf = " + outf);
					$muj("#mu-postbox-form-filename").val(outf);
				} else {
					$muj("#mu-postbox-form-filename").val("");
				}
			});
			document.getElementById('mu-postbox-form-submit').addEventListener("click",function(evt) {
				//alert("Received the click.");
				//$muj("#mu-postbox-top").html("Received the click.");
				var bigfile = document.getElementById('mu-postbox-form-bigfile');
				if(document.getElementById('mu-postbox-form-bigfile').files[0]) {
					file = document.getElementById('mu-postbox-form-bigfile').files[0];
					filename = $muj('#mu-postbox-form-filename').val();
					directory = $muj('#mu-postbox-form-directory').val();
					if(window.FileReader) {
						reader = new FileReader();
						reader.readAsDataURL(file);
					}	
					if(formdata) {
						formdata.append("file[]",file);
						formdata.append("filename",filename);
						formdata.append("siteurl",siteurl);
						formdata.append("directory",directory);
						<?php   if ($goodver) { 
									echo "formdata.append(\"" . ini_get("session.upload_progress.name") . "\", progname);\n"; 
								} ?>
						intvl = setInterval(function(){UploadText();},1000);
						$muj("#mu-postbox-top").html("This may take a while. Uploading...").show();
						
						$muj.ajax({
							url: ajaxpath,
							type: "POST",
							data: formdata,
							processData: false,
							contentType: false,
							success: function(data){
								clearInterval(intvl);
								$muj("#mu-postbox-top").html("").hide();
								if(data) {
									//alert("MassiveUpload reports that it has received the following data:\n"+data);
									outobj = JSON.parse(data);
									//alert("MassiveUpload reports that it has parsed the data.");
									if(outobj.error) {
										alert("WRITE THIS ERROR DOWN AND SEND IT TO YOUR SYSADMIN:\nMassiveUpload reports:\n"+outobj.error);
									} else if(outobj.success) {
										alert("MassiveUpload reports success!\n"+outobj.success);
										$muj("#mu-postbox-top").html("Success! " + outobj.success).show();
									} else {
										alert("WRITE THIS ERROR DOWN AND SEND IT TO YOUR SYSADMIN:\nMassiveUpload reports that it received data in the AJAX function, but it was incomprehensible.");
									}
								} else {
									alert("WRITE THIS ERROR DOWN AND SEND IT TO YOUR SYSADMIN:\nMassiveUpload reports:\nIn the AJAX function, there was no output object.");
								}
							}
						});
						//alert(".ajax didn't have any errors.");
					}
				} else {
					alert("You must select a file first.");
				}
				if(evt.preventDefault) {
					evt.preventDefault();
				}
				evt.returnValue = false;
				return false;
			}, false);
		});
		
		function AJAXUploadText(data) {
			if(data) {
				var output = JSON.parse(data);
				if(output.progress) {
					ultext = "This may take a while. Uploading: " + output.progress;
				} else {
					ultext = "This may take a while. Uploading... <img src=\"<?php echo admin_url( 'images/wpspin_light.gif' ); ?>\" style=\"vertical-align: middle;\">";
				}
			} else {
				ultext = "This may take a while. Uploading... <img src=\"<?php echo admin_url( 'images/wpspin_light.gif' ); ?>\" style=\"vertical-align: middle;\">";
			}
			$muj("#mu-postbox-top").html(ultext);

		}
		
		function UploadText(){
			var ultext = "This may take a while. Uploading...";
			<?php 	
			if($goodver) { ?>
				ajaxstr = "prog=" + progname;
				$muj.post(progpath,ajaxstr,function(data){AJAXUploadText(data);});
			<?php } else { ?>
			ultext = ultext + " <img src=\"<?php echo admin_url( 'images/wpspin_light.gif' ); ?>\" style=\"vertical-align: middle;\">\n";
/*			if(inti == 0) {
				inti = 1;
			} else if (inti == 1) {
				ultext = ultext + ".";
				inti = 2;
			} else if (inti == 2) {
				ultext = ultext + "..";
				inti = 0;
			} else {
				inti = 0;
			} */
			$muj("#mu-postbox-top").html(ultext); 
			<?php } ?>
		}	    
    </script>
    <?php 
	$mum = new MUMaster();
	$mup = new MUDiv();
	$mus = new MUCSS();
	$mus->AddProperty("#mu-postbox-top","background-color","#9999ff");
	$mus->AddProperty("#mu-postbox-top","border","2px solid #333399");
	$mus->AddProperty("#mu-postbox-top","color","#333399");
	$mus->AddProperty("#mu-postbox-top","display","none");
	$mus->AddProperty("#mu-postbox-top","margin","3px");
	$mus->AddProperty("#mu-postbox-top","padding","3px");
	$mus->GetCSS(true,true);
	$mup->addid("mu-postbox");
	$mup->append($mum->nonce("div","<strong>Upload your big files here!</strong>","mu-postbox-header"));
	$mup->append("<form id=\"mu-postbox-form\" action=\"ajax.php\" method=\"post\">");
	$mup->append("<input type=\"file\" name=\"files[]\" id=\"mu-postbox-form-bigfile\"/>");
	$mup->appendbr();
	$mup->append("Desired filename: <input type=\"text\" size=\"30\" id=\"mu-postbox-form-filename\" name=\"filename\">");
	$mup->appendbr();
	$mup->append("<input type=\"hidden\" name=\"directory\" id=\"mu-postbox-form-directory\" value=\"" . $defdir . "\">\n");
	$mup->append("<input type=\"submit\" value=\"Upload!\" id=\"mu-postbox-form-submit\">");
	$mup->appendbr();
	$mup->append($mum->nonce("div","","mu-postbox-top"));
	echo $mup;
}

// DEFINE SETTINGS

function MUp_init() {
	register_setting('mup_options','mup_options','MUp_validate');
	add_settings_section('mup_settings_defaults','Upload Defaults', 'MUp_settings_defaults','mup_settings');
	add_settings_field('mup_default_directory','Default upload directory','MUp_default_directory','mup_settings','mup_settings_defaults');
	add_settings_field('mup_default_filename','Default filename pattern','MUp_default_filename','mup_settings','mup_settings_defaults');
}

function MUp_settings_defaults() {
	echo "<p><strong>Default settings</strong></p>\n";
}

function MUp_default_directory() {
	$options = get_option('mup_options');
	echo "<input id=\"mup_default_directory_which0\" name=\"mup_options[default_directory_which]\" type=\"radio\" value=\"0\"";
	if(!$options['default_directory_which'] || intval($options['default_directory_which']) != 1) { echo " CHECKED"; }
	echo "> Use the default (wp-content/uploads/)\n<br>";
	echo "<input id=\"mup_default_directory_which1\" name=\"mup_options[default_directory_which]\" type=\"radio\" value=\"1\"";
	if(intval($options['default_directory_which']) == 1) { echo " CHECKED"; }
	echo "> Use this directory (relative to the Wordpress root directory; you can use ../)\n<br>";
	echo "<input id=\"mup_default_directory\" name=\"mup_options[default_directory]\" type=\"text\" size=\"40\" value=\"";
	if($options['default_directory'] && $options['default_directory'] != "") { echo $options['default_directory']; }
	echo "\">\n<br><span style=\"color: #333333;\"><em>This must be a valid, existing directory! The plugin will not work otherwise.</em></span><br>";
}

function MUp_default_filename() {
	$options = get_option('mup_options');
	echo "<input id=\"mup_default_filename\" name=\"mup_options[default_filename]\" type=\"text\" size=\"40\" value=\"";
	if($options['default_filename'] && $options['default_filename'] != "") { echo $options['default_filename']; }
	echo "\">.[extension]\n<br><span style=\"color: #333333;\"><em><strong>Do not include the extension!</strong> The plugin will do that for you.<br>\nYou can use A-Z, a-z, 0-9, -, and . in filenames. You can also include /, which will add a level to the final directory.<br>\n<br>\nYou can also use variable parameters:<br>%name% - current user's display name (with spaces removed)<br>\n%month% - month at time of upload (use %monnm% for numeric 01-12)<br>\n%day% - two-digit day of the month at time of upload<br>\n%year% - four-digit year at time of upload<br>\n%hour% - two-digit hour at time of upload<br>\n%minute% - two-digit minute at time of upload<br>\n";
}

// CREATE ADMIN PAGE

function MUp_doAdminPage() {
?>
	<div class="wrap">
    <h2>Massive Upload</h2>
    <form action="options.php" method="post">
	<?php 
	settings_fields('mup_options');
	$options = get_option('mup_options');
	do_settings_sections('mup_settings');	
	?>
    <br>
	<input name="Submit" type="submit" value="<?php esc_attr_e('Save Changes'); ?>" />
	</form>
    <?php
}

// WORDPRESS INTERACTION

function MUp_addMetaBox() {
	add_meta_box(
		'mup-upload', 
		'Massive Upload', 
		'MUp_upload', 
		'post', 
		'side', 
		'default'
	);
}

if (is_admin()) {
	add_action('admin_init', 'MUp_init');
	add_action('admin_menu', 'MUp_addAdminPage');
}
add_action('add_meta_boxes','MUp_addMetaBox');

?>