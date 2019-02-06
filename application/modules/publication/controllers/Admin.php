<?php
if (!defined('BASEPATH'))
exit('No direct script access allowed');

class Admin extends Admin_Controller {
	function __construct() {
		if(!$this->session->userdata('logged_in'))
		{
			redirect(ADMIN_LOGIN_PATH, 'refresh');exit;
		}
		$this->template->set_layout('admin/default');
		$this->load->model('Admin_dash_model');
		$this->load->dbforge();
		$this->load->model('Publication_model');
		$this->load->library('form_validation');
		$this->form_validation->set_error_delimiters('<div class="error">', '</div>');	
	}
	public function index()
	{
		# code...
	}
	public function view_publication(){
	  	$this->body= array();
	    $this->body['data']=$this->Publication_model->get_all_data();
	    //admin check
	    $admin_type=$this->session->userdata('user_type');
	    $this->body['admin']=$admin_type;
	    //admin check
	    $this->template
                        ->enable_parser(FALSE)
                        ->build('admin/publication_tbl',$this->body);

  	}
  	public function add_publication_category(){
	 	$this->data=array();
	 	$this->form_validation->set_rules('name', 'Publication Category Name', 'trim|required');
	 	$lang=$this->session->get_userdata('Language');
        if($lang['Language']=='en') {
            $emerg_lang='en';
        }else{
            $emerg_lang='nep'; 
        }
		if ($this->form_validation->run() == TRUE){
	      	$page_slug_new = strtolower (preg_replace('/[[:space:]]+/', '-', $this->input->post('name')));
	      	$data=array(
	        	'name'=>$this->input->post('name'),
	        	'language'=>$emerg_lang,
	        	'slug'=>$page_slug_new,
	      	);
	      	$insert=$this->Publication_model->add_publiactioncat('publicationcat',$data);
	      	if($insert!=""){
		        $this->session->set_flashdata('msg','Publication successfully added');
		        redirect(FOLDER_ADMIN.'/publication/add_publication_category');
	        }
	    }else{
	      //admin check
	    	$id = base64_decode($this->input->get('id'));
	    	//print_r($id);die;
	    	if($id) {
				$this->data['drrdataeditdata'] = $this->general->get_tbl_data_result('id,name','publicationcat',array('id'=>$id));
	    	}else{
	    		$this->data['drrdataeditdata'] = array();	
	    	}
	    	$this->data['publicationdata'] = $this->general->get_tbl_data_result('id,name','publicationcat');	
	      	$admin_type=$this->session->userdata('user_type');
	      	$this->data['admin']=$admin_type;
	      	//admin check
	      	$this->template
	                        ->enable_parser(FALSE)
	                        ->build('admin/index',$this->data);
	    }
	}
 	public function add_publication(){
 		$this->body=array();
 		
 		
    	$this->form_validation->set_rules('category', 'Please Select Hazard category', 'trim|required');
    	$this->form_validation->set_rules('type', 'Please Select File Type', 'trim|required');
		if ($this->form_validation->run() == TRUE){
	      	$file_name = $_FILES['proj_pic']['name'];
	      	$attachment=$_FILES['uploadedfile']['name'];
	    	// $ext = pathinfo($file_name, PATHINFO_EXTENSION);
	      	$ext_file = pathinfo($attachment, PATHINFO_EXTENSION);
	      	$data=array(
	        	'title'=>$this->input->post('title'),
	        	'summary'=>$this->input->post('summary'),
	        	'type'=>$this->input->post('type'),
	        	'category'=>$this->input->post('category'),
	        	'videolink'=>$this->input->post('videolink'),
	      	);
	      	$insert=$this->Publication_model->add_publication('publication',$data);
	      	if($insert!=""){
	        	$img_upload=$this->Publication_model->do_upload($file_name,$insert);
	        	$file_upload=$this->Publication_model->file_do_upload($attachment,$insert);
	        	// var_dump($file_upload);
	        	// exit();
	        	if($img_upload['status']){
			        if($img_upload['status']== 1){
					//var_dump($img_upload);
			          $ext=$img_upload['upload_data']['file_ext'];
			        //echo $ext;
			      	//  exit();
			          $image_path=base_url() . 'uploads/publication/'.$insert.$ext ;
			          $file_path=base_url() . 'uploads/publication/file/'.$insert.'.'.$ext_file ;
			          $img=array(
			            'photo'=>$image_path,
			            'file'=>$file_path
			          );
			          $update_path=$this->Publication_model->update_path($insert,$img);
			          $this->load->model('Newsletter');
			          $mail_subject='New Publication Added in VSO Webpage';
			          $m='New Publication '.$this->input->post('title').' has been added in VSO Webpage.Plese follow link to view new Publication <br>'.base_url().'publication';
			          $this->Newsletter->send_mail($m,$mail_subject);
			          $this->session->set_flashdata('msg','Publication successfully added');
			          // redirect('view_publication');
			          redirect(FOLDER_ADMIN.'/publication/view_publication');
			        }else{
			          $code= strip_tags($img_upload['error']);
			          $this->session->set_flashdata('msg', $code);
			          redirect(FOLDER_ADMIN.'/publication/add_publication');
			          // redirect('add_publication');
			        }
			    }else{
			    	redirect(FOLDER_ADMIN.'/publication/view_publication');
			    }
	        }
	    }else{
	      //admin check
	      $admin_type=$this->session->userdata('user_type');
	      $this->data['admin']=$admin_type;
	      //admin check
	     
	      $this->data['pub'] = $this->general->get_tbl_data_result('id,name','publicationcat');
	      // echo "<pre>"; print_r($this->data['pub']);die;
	      $this->template
	                        ->enable_parser(FALSE)
	                        ->build('admin/add_publication',$this->data);
	      // $this->load->view('admin/header',$this->body);
	      // $this->load->view('admin/add_publication');
	      // $this->load->view('admin/footer');
	    }
  }
    public function edit_publication(){
	   $this->body=array();
	    $id=base64_decode($this->input->get('id'));
	    $this->body['pub'] = $this->general->get_tbl_data_result('*','publication');
	    if(isset($_POST['submit'])){
	      if( $_FILES['proj_pic']['name']==''){
	        $data=array(
	        	'title'=>$this->input->post('title'),
	        	'summary'=>$this->input->post('summary'),
	        	'type'=>$this->input->post('type'),
	        	'category'=>$this->input->post('category'),
	        	'videolink'=>$this->input->post('videolink'),
	      	);
	        $update=$this->Publication_model->update_data($id,$data);
	        if($update==1){
	          $this->session->set_flashdata('msg','Data successfully Updated');
	          // redirect('view_publication');
	          redirect(FOLDER_ADMIN.'/publication/view_publication');
	        }
	      }else{
	        $file_name = $_FILES['proj_pic']['name'];
	        $ext = pathinfo($file_name, PATHINFO_EXTENSION);
	       $data=array(
	        	'title'=>$this->input->post('title'),
	        	'summary'=>$this->input->post('summary'),
	        	'category'=>$this->input->post('category'),
	        	'videolink'=>$this->input->post('videolink'),
	      	);
	        $insert=$this->Publication_model->update_data($id,$data);
	        if($insert==1){
	          $img_upload=$this->Publication_model->do_upload($file_name,$id);
	          if($img_upload==1){
	            $image_path=base_url() . 'uploads/publication/'.$id.'.'.$ext ;
	            $img=array(
	              'photo'=>$image_path,
	            );
	            $update_path=$this->Publication_model->update_path($id,$img);
	            $this->session->set_flashdata('msg','Publication successfully Updated');
	            // redirect('view_publication');
	            redirect(FOLDER_ADMIN.'/publication/view_publication');
	          }else{
	            $code= strip_tags($img_upload['error']);
	            $this->session->set_flashdata('msg', $code);
	            // redirect('add_publication');
          		redirect(FOLDER_ADMIN.'/publication/add_publication');

	          }
	        }else{
	          //db error
	        }
	      }
	    }else{

	      $this->body['edit_data']=$this->Publication_model->get_edit_data(base64_decode($this->input->get('id')),'publication');
	      //admin check
	      $admin_type=$this->session->userdata('user_type');
	      $this->body['admin']=$admin_type;
	      //admin check
	      $this->template
                        ->enable_parser(FALSE)
                        ->build('admin/edit_publication',$this->body);
	      // $this->load->view('admin/header',$this->body);
	      // $this->load->view('admin/edit_publication',$this->body);
	      // $this->load->view('admin/footer');
	    }
  }
    public function delete_publication(){
	    $id = $this->input->get('id');
	    $delete=$this->Publication_model->delete_data($id);

	    $this->session->set_flashdata('msg','Id number '.$id.' row data was deleted successfully');
	    // redirect('view_publication');
	    redirect(FOLDER_ADMIN.'/publication/view_publication');
  	}
}