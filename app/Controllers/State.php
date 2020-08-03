<?php namespace App\Controllers;

use App\Models\SettingModel;
use App\Models\StateModel;

class State extends BaseController
{
	public function __construct()
	{
		$this->setting = new SettingModel();
		$this->model = new StateModel();
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
				'state' => $this->model->getState(),
				'checkLevel' => $checkLevel
			);
			echo view('layout/header', $data);
			echo view('state/view_state', $data);
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
				'request' => $this->request,
				'time_zone' => $this->model->getTimeZone(),
				'geo_unit' => $this->model->getGeoUnit()
			);
			$validation = $this->validate([
				'time_zone' => ['label' => 'Time Zone', 'rules' => 'required'],
				'geo_unit' => ['label' => 'Geographical Unit', 'rules' => 'required'],
				'state_alpha2_code' => ['label' => 'Alpha-2 Code', 'rules' => 'required|alpha|min_length[2]|max_length[2]|is_unique[state.state_alpha2_code]'],
				'state_numeric_code' => ['label' => 'Numeric Code', 'rules' => 'required|numeric|min_length[2]|max_length[2]|is_unique[state.state_numeric_code]'],
				'state_name' => ['label' => 'Name', 'rules' => 'required'],
				'state_capital' => ['label' => 'Capital', 'rules' => 'permit_empty']
			]);
			if(!$validation){
				$data['validation'] = $this->validator;
				echo view('layout/header', $data);
				echo view('state/form_add_state', $data);
				echo view('layout/footer');
			}else{
				$stateData = array(
					'state_id' => $this->model->getStateId(),
					'tz_id' => $this->request->getPost('time_zone'),
					'geo_unit_id' => $this->request->getPost('geo_unit'),
					'state_alpha2_code' => $this->request->getPost('state_alpha2_code'),
					'state_numeric_code' => $this->request->getPost('state_numeric_code'),
					'state_name' => $this->request->getPost('state_name'),
					'state_capital' => $this->request->getPost('state_capital'),
					'state_status' => 1
				);
				$this->model->insertState($stateData);
				session()->setFlashdata('success', 'State has been added successfully. (SysCode: <a href="'.base_url('state?id='.$stateData['state_id']).'" class="alert-link">'.$stateData['state_id'].'</a>)');
				return redirect()->to(base_url('state'));
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
				'state_csv' => ['label' => 'Upload CSV File', 'rules' => 'uploaded[state_csv]|ext_in[state_csv,csv]|max_size[state_csv,2048]']
			]);
			$data = array(
				'title' => @$checkMenu->menu_name,
				'breadcrumb' => @$checkMenu->mgroup_name,
				'validation' => $this->validator
			);
			if(!$validation){
				echo view('layout/header', $data);
				echo view('state/form_bulk_upload_state');
				echo view('layout/footer');
			}else{
				$state_csv = $this->request->getFile('state_csv')->getTempName();
				$file = file_get_contents($state_csv);
				$lines = explode("\n", $file);
				$head = str_getcsv(array_shift($lines));
				$data['state'] = array();
				foreach ($lines as $line) {
					$data['state'][] = array_combine($head, str_getcsv($line));
				}
				$data['model'] = $this->model;
				echo view('layout/header', $data);
				echo view('state/form_bulk_upload_state', $data);
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
				'state.*.time_zone' => ['label' => 'Time Zone', 'rules' => 'required'],
				'state.*.geo_unit' => ['label' => 'Geographical Unit', 'rules' => 'required'],
				'state.*.state_alpha2_code' => ['label' => 'Alpha-2 Code', 'rules' => 'required|alpha|min_length[2]|max_length[2]|is_unique[state.state_alpha2_code]'],
				'state.*.state_numeric_code' => ['label' => 'Numeric Code', 'rules' => 'required|numeric|min_length[2]|max_length[2]|is_unique[state.state_numeric_code]'],
				'state.*.state_name' => ['label' => 'Name', 'rules' => 'required'],
				'state.*.state_capital' => ['label' => 'Capital', 'rules' => 'permit_empty']
			]);
			if(!$validation){
				session()->setFlashdata('warning', 'The CSV file you uploaded contains some errors.'.$this->validator->listErrors());
				return redirect()->to(base_url('state/bulk_upload'));
			}else{
				foreach ($this->request->getPost('state') as $row) {
					$stateData = array(
						'state_id' => $this->model->getStateId(),
						'tz_id' => $row['time_zone'],
						'geo_unit_id' => $row['geo_unit'],
						'state_alpha2_code' => $row['state_alpha2_code'],
						'state_numeric_code' => $row['state_numeric_code'],
						'state_name' => $row['state_name'],
						'state_capital' => $row['state_capital'],
						'state_status' => 1
					);
					$this->model->insertState($stateData);
				}
				session()->setFlashdata('success', 'States has been added successfully.');
				return redirect()->to(base_url('state'));
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
				'time_zone' => $this->model->getTimeZone(),
				'geo_unit' => $this->model->getGeoUnit(),
				'state' => $this->model->getStateById($id)
			);
			if($data['state']->state_alpha2_code == $this->request->getPost('state_alpha2_code')){
				$state_alpha2_code_rules = 'required|alpha|min_length[2]|max_length[2]';
			}else{
				$state_alpha2_code_rules = 'required|alpha|min_length[2]|max_length[2]|is_unique[state.state_alpha2_code]';
			}
			if($data['state']->state_numeric_code == $this->request->getPost('state_numeric_code')){
				$state_numeric_code_rules = 'required|numeric|min_length[3]|max_length[3]';
			}else{
				$state_numeric_code_rules = 'required|numeric|min_length[3]|max_length[3]|is_unique[state.state_numeric_code]';
			}
			$validation = $this->validate([
				'time_zone' => ['label' => 'Time Zone', 'rules' => 'required'],
				'geo_unit' => ['label' => 'Geographical Unit', 'rules' => 'required'],
				'state_alpha2_code' => ['label' => 'Alpha-2 Code', 'rules' => $state_alpha2_code_rules],
				'state_numeric_code' => ['label' => 'Numeric Code', 'rules' => $state_numeric_code_rules],
				'state_name' => ['label' => 'Name', 'rules' => 'required'],
				'state_capital' => ['label' => 'Capital', 'rules' => 'permit_empty'],
				'status' => ['label' => 'Status', 'rules' => 'required']
			]);
			if(!$validation){
				$data['validation'] = $this->validator;
				echo view('layout/header', $data);
				echo view('state/form_edit_state', $data);
				echo view('layout/footer');
			}else{
				$stateData = array(
					'tz_id' => $this->request->getPost('time_zone'),
					'geo_unit_id' => $this->request->getPost('geo_unit'),
					'state_alpha2_code' => $this->request->getPost('state_alpha2_code'),
					'state_numeric_code' => $this->request->getPost('state_numeric_code'),
					'state_name' => $this->request->getPost('state_name'),
					'state_capital' => $this->request->getPost('state_capital'),
					'state_status' => $this->request->getPost('status')
				);
				$this->model->updateState($id, $stateData);
				session()->setFlashdata('success', 'State has been updated successfully. (SysCode: <a href="'.base_url('state?id='.$id).'" class="alert-link">'.$id.'</a>)');
				return redirect()->to(base_url('state'));
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
			$stateData = $this->model->getStateById($id);
			$this->model->deleteState($id);
			session()->setFlashdata('warning', 'State has been removed successfully. <a href="'.base_url('state/undo?data='.json_encode($stateData)).'" class="alert-link">Undo</a>');
			return redirect()->to(base_url('state'));
		}else{
			session()->setFlashdata('warning', 'Sorry, You are not allowed to access this page.');
			return redirect()->to(base_url('login?redirect='.@$checkMenu->menu_url));
		}
	}

	public function undo()
	{
		$state = json_decode($this->request->getGet('data'));
		$checkState = $this->model->getStateById(@$state->state_id);
		if(@$checkState){
			$state_id = $this->model->getStateId();
		}else{
			$state_id = @$state->state_id;
		}
		$stateData = array(
			'state_id' => $state_id,
			'tz_id' => @$state->tz_id,
			'geo_unit_id' => @$state->geo_unit_id,
			'state_alpha2_code' => @$state->state_alpha2_code,
			'state_numeric_code' => @$state->state_numeric_code,
			'state_name' => @$state->state_name,
			'state_capital' => @$state->state_capital,
			'state_status' => @$state->state_status
		);
		$this->model->insertState($stateData);
		session()->setFlashdata('success', 'Action undone. (SysCode: <a href="'.base_url('state?id='.$state_id).'" class="alert-link">'.$state_id.'</a>)');
		return redirect()->to(base_url('state'));
	}
}