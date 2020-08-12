<?php namespace App\Controllers;

use App\Models\SettingModel;
use App\Models\LanguageModel;

class Language extends BaseController
{
	public function __construct()
	{
		$this->setting = new SettingModel();
		$this->model = new LanguageModel();
	}

	public function index()
	{
		$checkMenu = $this->setting->getMenuByUrl($this->request->uri->getSegment(1));
		$checkLevel = $this->setting->getLevelByRole('L12000001', @$checkMenu->menu_id);
		if(@$checkLevel->read == 1){
			$data = array(
				'title' =>  @$checkMenu->menu_name,
				'breadcrumb' => @$checkMenu->mgroup_name,
				'model' => $this->model,
				'language' => $this->model->getLanguage(),
				'checkLevel' => $checkLevel
			);
			echo view('layout/header', $data);
			echo view('language/view_language', $data);
			echo view('layout/footer');
		}else{
			session()->setFlashdata('warning', 'Sorry, You are not allowed to access this page.');
			return redirect()->to(base_url('login?redirect='.@$checkMenu->menu_url));
		}
	}

	public function add()
	{
		$checkMenu = $this->setting->getMenuByUrl($this->request->uri->getSegment(1));
		$checkLevel = $this->setting->getLevelByRole('L12000001', @$checkMenu->menu_id);
		if(@$checkLevel->create == 1){
			$data = array(
				'title' => @$checkMenu->menu_name,
				'breadcrumb' => @$checkMenu->mgroup_name,
				'request' => $this->request
			);
			$validation = $this->validate([
				'lang_code' => ['label' => 'Code', 'rules' => 'required|alpha|min_length[2]|max_length[2]|is_unique[language.lang_code]'],
				'lang_name' => ['label' => 'Name', 'rules' => 'required']
			]);
			if(!$validation){
				$data['validation'] = $this->validator;
				echo view('layout/header', $data);
				echo view('language/form_add_language', $data);
				echo view('layout/footer');
			}else{
				$languageData = array(
					'lang_id' => $this->model->getLanguageId(),
					'lang_code' => $this->request->getPost('lang_code'),
					'lang_name' => $this->request->getPost('lang_name'),
					'lang_status' => 1
				);
				$this->model->insertLanguage($languageData);
				session()->setFlashdata('success', 'Language has been added successfully. (SysCode: <a href="'.base_url('language?id='.$languageData['lang_id']).'" class="alert-link">'.$languageData['lang_id'].'</a>)');
				return redirect()->to(base_url('language'));
			}
		}else{
			session()->setFlashdata('warning', 'Sorry, You are not allowed to access this page.');
			return redirect()->to(base_url('login?redirect='.@$checkMenu->menu_url));
		}
	}

	public function bulk_upload()
	{
		$checkMenu = $this->setting->getMenuByUrl($this->request->uri->getSegment(1));
		$checkLevel = $this->setting->getLevelByRole('L12000001', @$checkMenu->menu_id);
		if(@$checkLevel->create == 1){
			$validation = $this->validate([
				'language_csv' => ['label' => 'Upload CSV File', 'rules' => 'uploaded[language_csv]|ext_in[language_csv,csv]|max_size[language_csv,2048]']
			]);
			$data = array(
				'title' => @$checkMenu->menu_name,
				'breadcrumb' => @$checkMenu->mgroup_name,
				'validation' => $this->validator
			);
			if(!$validation){
				echo view('layout/header', $data);
				echo view('language/form_bulk_upload_language');
				echo view('layout/footer');
			}else{
				$language_csv = $this->request->getFile('language_csv')->getTempName();
				$file = file_get_contents($language_csv);
				$lines = explode("\n", $file);
				$head = str_getcsv(array_shift($lines));
				$data['language'] = array();
				foreach ($lines as $line) {
					$data['language'][] = array_combine($head, str_getcsv($line));
				}
				$data['model'] = $this->model;
				echo view('layout/header', $data);
				echo view('language/form_bulk_upload_language', $data);
				echo view('layout/footer');
			}
		}else{
			session()->setFlashdata('warning', 'Sorry, You are not allowed to access this page.');
			return redirect()->to(base_url('login?redirect='.@$checkMenu->menu_url));
		}
	}

	public function bulk_save()
	{
		$checkMenu = $this->setting->getMenuByUrl($this->request->uri->getSegment(1));
		$checkLevel = $this->setting->getLevelByRole('L12000001', @$checkMenu->menu_id);
		if(@$checkLevel->create == 1){
			$validation = $this->validate([
				'language.*.lang_code' => ['label' => 'Code', 'rules' => 'required|alpha|min_length[2]|max_length[2]|is_unique[language.lang_code]'],
				'language.*.lang_name' => ['label' => 'Name', 'rules' => 'required']
			]);
			if(!$validation){
				session()->setFlashdata('warning', 'The CSV file you uploaded contains some errors.'.$this->validator->listErrors());
				return redirect()->to(base_url('language/bulk_upload'));
			}else{
				foreach ($this->request->getPost('language') as $row) {
					$languageData = array(
						'lang_id' => $this->model->getLanguageId(),
						'lang_code' => $row['lang_code'],
						'lang_name' => $row['lang_name'],
						'lang_status' => 1
					);
					$this->model->insertLanguage($languageData);
				}
				session()->setFlashdata('success', 'Currencies has been added successfully.');
				return redirect()->to(base_url('language'));
			}
		}else{
			session()->setFlashdata('warning', 'Sorry, You are not allowed to access this page.');
			return redirect()->to(base_url('login?redirect='.@$checkMenu->menu_url));
		}
	}

	public function edit($id)
	{
		$checkMenu = $this->setting->getMenuByUrl($this->request->uri->getSegment(1));
		$checkLevel = $this->setting->getLevelByRole('L12000001', @$checkMenu->menu_id);
		if(@$checkLevel->update == 1){
			$data = array(
				'title' => @$checkMenu->menu_name,
				'breadcrumb' => @$checkMenu->mgroup_name,
				'request' => $this->request,
				'language' => $this->model->getLanguageById($id)
			);
			if($data['language']->lang_code == $this->request->getPost('lang_code')){
				$language_code_rules = 'required|alpha|min_length[2]|max_length[2]';
			}else{
				$language_code_rules = 'required|alpha|min_length[2]|max_length[2]|is_unique[language.lang_code]';
			}
			$validation = $this->validate([
				'lang_code' => ['label' => 'Code', 'rules' => $language_code_rules],
				'lang_name' => ['label' => 'Name', 'rules' => 'required'],
				'status' => ['label' => 'Status', 'rules' => 'required']
			]);
			if(!$validation){
				$data['validation'] = $this->validator;
				echo view('layout/header', $data);
				echo view('language/form_edit_language', $data);
				echo view('layout/footer');
			}else{
				$languageData = array(
					'lang_code' => $this->request->getPost('lang_code'),
					'lang_name' => $this->request->getPost('lang_name'),
					'lang_status' => $this->request->getPost('status')
				);
				$this->model->updateLanguage($id, $languageData);
				session()->setFlashdata('success', 'Language has been updated successfully. (SysCode: <a href="'.base_url('language?id='.$id).'" class="alert-link">'.$id.'</a>)');
				return redirect()->to(base_url('language'));
			}
		}else{
			session()->setFlashdata('warning', 'Sorry, You are not allowed to access this page.');
			return redirect()->to(base_url('login?redirect='.@$checkMenu->menu_url));
		}
	}

	public function delete($id)
	{
		$checkMenu = $this->setting->getMenuByUrl($this->request->uri->getSegment(1));
		$checkLevel = $this->setting->getLevelByRole('L12000001', @$checkMenu->menu_id);
		if(@$checkLevel->delete == 1){
			$languageData = $this->model->getLanguageById($id);
			$this->model->deleteLanguage($id);
			session()->setFlashdata('warning', 'Language has been removed successfully. <a href="'.base_url('language/undo?data='.json_encode($languageData)).'" class="alert-link">Undo</a>');
			return redirect()->to(base_url('language'));
		}else{
			session()->setFlashdata('warning', 'Sorry, You are not allowed to access this page.');
			return redirect()->to(base_url('login?redirect='.@$checkMenu->menu_url));
		}
	}

	public function undo()
	{
		$language = json_decode($this->request->getGet('data'));
		$checkLanguage = $this->model->getLanguageById(@$language->lang_id);
		if(@$checkLanguage){
			$lang_id = $this->model->getLanguageId();
		}else{
			$lang_id = @$language->lang_id;
		}
		$languageData = array(
			'lang_id' => $lang_id,
			'lang_code' => @$language->lang_code,
			'lang_name' => @$language->lang_name,
			'lang_status' => @$language->lang_status
		);
		$this->model->insertLanguage($languageData);
		session()->setFlashdata('success', 'Action undone. (SysCode: <a href="'.base_url('language?id='.$lang_id).'" class="alert-link">'.$lang_id.'</a>)');
		return redirect()->to(base_url('language'));
	}
}
