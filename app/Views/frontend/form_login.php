	<!-- begin #page-container -->
	<div id="page-container" class="fade">
		<!-- begin login -->
		<div class="login login-with-news-feed">
			<!-- begin news-feed -->
			<div class="news-feed">
				<div class="news-image" style="background-image: url(<?php echo base_url('assets/img/login-bg/login-bg-11.jpg'); ?>)"></div>
				<div class="news-caption">
					<h4 class="caption-title"><?= $nav_brand ?></h4>
					<p>Download the <?= $web_title; ?> for iOS and Android™.</p>
				</div>
			</div>
			<!-- end news-feed -->
			<!-- begin right-content -->
			<div class="right-content">
				<!-- begin login-header -->
				<div class="login-header">
					<div class="brand">
					<?php 
						$navbar_logo = $setting->getSettingById(8)->setting_value;
						if(@$navbar_logo){ ?>
						<img src="<?php echo base_url('assets/img/logo/'.$navbar_logo); ?>" class="img-rounded height-30" />&nbsp;
					<?php }else{ ?>
						<span class="logo"></span>
					<?php } ?>
					<?= $nav_brand ?>
						<small><?= $meta_desc; ?></small>
					</div>
					<div class="icon">
						<i class="fa fa-sign-in-alt"></i>
					</div>
				</div>
				<!-- end login-header -->
				<!-- begin login-content -->
				<div class="login-content">
					<?php
						$success_alert = session()->getFlashdata('success');
						$info_alert = session()->getFlashdata('info');
						$warning_alert = session()->getFlashdata('warning');
						$danger_alert = session()->getFlashdata('danger');
						if($success_alert OR $info_alert OR $warning_alert OR $danger_alert){
							if($success_alert){
								$alert_color = 'success';
								$alert_message = '<strong>Success!</strong> '.$success_alert;
							}elseif($info_alert){
								$alert_color = 'info';
								$alert_message = '<strong>Info!</strong> '.$info_alert;
							}elseif($warning_alert){
								$alert_color = 'warning';
								$alert_message = '<strong>Warning!</strong> '.$warning_alert;
							}elseif($danger_alert){
								$alert_color = 'danger';
								$alert_message = '<strong>Error!</strong> '.$danger_alert;
							}
					?>
					<div class="alert alert-<?= $alert_color ?>">
						<button class="close" data-dismiss="alert">&times;</button>
						<?= $alert_message ?>
					</div>
					<?php } ?>
					<form action="<?php echo base_url('login?redirect='.@$request->getGet('redirect')); ?>" method="post" class="margin-bottom-0">
						<?php $error = $validation->getError('user_email'); ?>
						<div class="form-group m-b-15">
							<input type="text" class="form-control form-control-lg <?php if($error){ echo 'is-invalid'; } ?>" name="user_email" value="<?= $request->getPost('user_email'); ?>" placeholder="Email Address" />
							<?php if($error){ echo '<div class="invalid-feedback">'.$error.'</div>'; } ?>
						</div>
						<?php $error = $validation->getError('user_password'); ?>
						<div class="form-group m-b-15">
							<input type="password" class="form-control form-control-lg <?php if($error){ echo 'is-invalid'; } ?>" name="user_password" placeholder="Password" />
							<?php if($error){ echo '<div class="invalid-feedback">'.$error.'</div>'; } ?>
						</div>
						<!--<div class="checkbox checkbox-css m-b-30">
							<input type="checkbox" id="remember_me_checkbox" value="" />
							<label for="remember_me_checkbox">
							Remember Me
							</label>
						</div>-->
						<?php $error = $validation->getError('g-recaptcha-response'); ?>
						<div class="m-b-15">
							<div class="g-recaptcha <?php if($error){ echo 'is-invalid'; } ?>" data-sitekey="<?= @$setting->getSettingById(18)->setting_value; ?>"></div>
							<?php if($error){ echo '<div class="invalid-feedback">'.$error.'</div>'; } ?>
						</div>
						<div class="login-buttons">
							<button type="submit" class="btn btn-success btn-block btn-lg">Sign in</button>
							<div class="m-t-10 m-b-10 text-center">or</div>
							<a href="javascript:;" class="btn btn-social btn-block btn-google">
								<span class="fab fa-google"></span> <div class="text-center">Sign in with Google</div>
							</a>
						</div>
						<div class="m-t-20 m-b-40 p-b-40 text-inverse">
						<?php if(@$setting->getSettingById(10)->setting_value == 1){ ?>
							New user? Click <a href="<?php echo base_url('register'); ?>">here</a> to register.<br>
						<?php } if(@$setting->getSettingById(11)->setting_value == 1){ ?>
							Forgot password? Click <a href="<?php echo base_url('forgot_password'); ?>">here</a> to reset password.
						<?php } ?>
						</div>
						<hr />
						<p class="text-center text-grey-darker mb-0">
							<?= @$setting->getSettingById(6)->setting_value; ?>
						</p>
					</form>
				</div>
				<!-- end login-content -->
			</div>
			<!-- end right-container -->
		</div>
		<!-- end login -->
		
		<!-- begin scroll to top btn -->
		<a href="javascript:;" class="btn btn-icon btn-circle btn-success btn-scroll-to-top fade" data-click="scroll-top"><i class="fa fa-angle-up"></i></a>
		<!-- end scroll to top btn -->
	</div>
	<!-- end page container -->