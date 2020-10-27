<?php namespace App\Controllers;

use App\Models\SettingModel;
use App\Models\UserModel;

class Login extends BaseController
{
	public function __construct()
	{
		$this->setting = new SettingModel();
		$this->model = new UserModel();
		
		$this->email = \Config\Services::email();
	}

	public function index()
	{
		$validation = $this->validate([
			'user_email' => ['label' => 'Email Address', 'rules' => 'required|valid_email'],
			'user_password' => ['label' => 'Password', 'rules' => 'required|min_length[6]']
		]);
		$data = array(
			'setting' => $this->setting,
			'title' =>  'Login',
			'request' => $this->request,
			'validation' => $this->validator
		);
		if(!$validation){
			echo view('frontend/form_login', $data);
		}else{
			$user_email = $this->request->getPost('user_email');
			$user_password = hash('sha256', $this->request->getPost('user_password'));
			$valid_user = $this->model->login($user_email, $user_password);

			if(@$this->request->getGet('redirect')){
				$login_redirect = '?redirect='.$this->request->getGet('redirect');
				$first_page = $this->request->getGet('redirect');
			}elseif(@$valid_user->menu_url){
				$first_page = $valid_user->menu_url;
			}else{
				$first_page = '';
			}

			if(!$valid_user){
				session()->setFlashdata('warning', 'Invalid username or password.');
				return redirect()->to(base_url('login'.@$login_redirect));
			}else{
				if($valid_user->user_status == 0){
					session()->setFlashdata('warning', 'Your account has been blocked.');
					return redirect()->to(base_url('login'.@$login_redirect));
				}elseif($valid_user->email_verification == 0){
					session()->setFlashdata('warning', 'Email address is not verified.');
					return redirect()->to(base_url('login'.@$login_redirect));
				}else{
					$session_data = array(
						'appid' => @$this->setting->getSettingById(0)->setting_value,
						'user_id' => $valid_user->user_id,
						'level_id' => $valid_user->level_id,
						'full_name' => $valid_user->first_name.' '.$valid_user->last_name,
						'level_name' => $valid_user->level_name,
						'user_photo' => $valid_user->user_photo_name,
						'tz_name' => $valid_user->tz_name
					);
					session()->set($session_data);

					$userHistoryData = array(
						'uhistory_id' => $this->model->getUserHistoryId(),
						'user_id' => $session_data['user_id'],
						'uhistory_action' => 'Sign In',
						'uhistory_time' => date('Y-m-d H:i:s')
					);
					$this->model->insertUserHistory($userHistoryData);

					return redirect()->to(base_url($first_page));
				}
			}
		}
	}

	public function logout()
	{
		if(session()->has('user_id')){
			$userHistoryData = array(
				'uhistory_id' => $this->model->getUserHistoryId(),
				'user_id' => session('user_id'),
				'uhistory_action' => 'Sign Out',
				'uhistory_time' => date('Y-m-d H:i:s')
			);
			$this->model->insertUserHistory($userHistoryData);
		}

		session()->destroy();
		return redirect()->to(base_url('login'));
	}

	public function verify_email($id)
	{
		$user = $this->model->getUserById($id);
		if(@$user->email_verification == 0 AND @$user->user_status == 1){
			$userData = array(
				'email_verification' => 1
			);
			$this->model->updateUser($id, $userData);

			$userHistoryData = array(
				'uhistory_id' => $this->model->getUserHistoryId(),
				'user_id' => $id,
				'uhistory_action' => 'Verify Email Address',
				'uhistory_time' => date('Y-m-d H:i:s')
			);
			$this->model->insertUserHistory($userHistoryData);

			$notifData = array(
				'notif_id' => $this->setting->getNotifId(),
				'sender_id' => $id,
				'recipient_id' => 'U120091600001',
				'notif_class' => 'fa fa-envelope media-object bg-silver-darker',
				'notif_title' => 'Verify Email Address',
				'notif_desc' => 'Email: '.@$user->user_email.' has been verified.',
				'notif_url' => 'user?id='.@$user->user_id,
				'notif_date' => date('Y-m-d H:i:s'),
				'notif_data' => @$user->user_email,
				'is_read' => 0
			);
			$this->setting->insertNotif($notifData);

			session()->setFlashdata('success', 'Your Email Address is successfully verified! Please login to access your account.');
		}
		return redirect()->to(base_url('login'));
	}
}
