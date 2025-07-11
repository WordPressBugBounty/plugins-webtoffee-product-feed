<?php
/**
 * History of import/Export
 *
 * @link           
 *
 * @package  Webtoffee_Product_Feed_Sync_History 
 */
if (!defined('ABSPATH')) {
    exit;
}
if(!class_exists('Webtoffee_Product_Feed_Sync_History')){
class Webtoffee_Product_Feed_Sync_History
{
	public $module_id='';
	public static $module_id_static='';
	public $module_base='history';
	public static $status_arr=array();
	public static $status_label_arr=array();
	public static $action_label_arr=array();
	public $max_records=50;
	
	public function __construct()
	{
		$this->module_id=Webtoffee_Product_Feed_Sync::get_module_id($this->module_base);
		self::$module_id_static=$this->module_id;

		self::$status_arr=array(
			'pending'=>0, //running...
			'finished'=>1, //completed
			'failed'=>2, //failed
		);

		add_action( 'init', array( $this, 'wt_product_feed_load_translations_history' ) );

		/* Admin menu for hostory listing */
		add_filter('wt_pf_admin_menu_basic', array($this, 'add_admin_pages'), 10, 1);

		/* advanced plugin settings */
		add_filter('wt_pf_advanced_setting_fields_basic', array($this, 'advanced_setting_fields'), 12);

		/* main ajax hook. The callback function will decide which action is to execute. */
		add_action('wp_ajax_iew_history_ajax_basic', array($this, 'ajax_main'), 11);

		/* Hook to perform actions after advanced settings was updated */
		add_action('wt_pf_after_advanced_setting_update_basic', array($this, 'after_advanced_setting_update'), 11);

		/* Download log file via nonce URL */
		add_action('admin_init', array($this, 'download_file'), 11);

	}

	/**
	 * Load translations.
	 */
	public function wt_product_feed_load_translations_history() {
		self::$status_label_arr=array(
			0=>__('Running/Incomplete', 'webtoffee-product-feed'),
			1=>__('Finished', 'webtoffee-product-feed'),
			2=>__('Failed', 'webtoffee-product-feed'),
		);

		self::$action_label_arr=array(
			'export'=>__('Export', 'webtoffee-product-feed'),
			'import'=>__('Import', 'webtoffee-product-feed'),
			'export_image'=>__('Image Export', 'webtoffee-product-feed'),
		);
	}
	/**
	 * Adding admin menus
	 */
	public function add_admin_pages($menus)
	{
		$menus[$this->module_base]=array(
			'submenu',
			WEBTOFFEE_PRODUCT_FEED_ID,
			__('Manage Feeds', 'webtoffee-product-feed'),
			__('Manage Feeds', 'webtoffee-product-feed'),
			apply_filters('wt_import_export_allowed_capability', 'import'),
			$this->module_id,
			array($this, 'admin_settings_page')
		);
		return $menus;
	}

	public function ajax_main()
	{
		if(Wt_Pf_Sh::check_write_access(WEBTOFFEE_PRODUCT_FEED_ID))
		{
			$allowed_ajax_actions=array('view_log');

			$out=array(
				'status'=>0,
				'msg'=>__('Error'),
			);

			$history_action=Wt_Pf_Sh::sanitize_item($_POST['history_action'], 'text');
			$data_type=Wt_Pf_Sh::sanitize_item($_POST['data_type'], 'text');

			if(method_exists($this, $history_action) && in_array($history_action, $allowed_ajax_actions))
			{
				$out=$this->{$history_action}($out);
			}

			if($data_type=='json')
			{
				echo json_encode($out);
			}
		}
		exit();
	}

	/**
	*	Fields for advanced settings
	*
	*/ 
	public function advanced_setting_fields($fields)
	{
		/*
		$fields['advanced_field_head'] =array(
				'type'=>'field_group_head', //field type
				'head'=>__('Advanced options'),
				'group_id'=>'advanced_field', //field group id
				'show_on_default'=>0,
			);
		$fields['enable_history_auto_delete']=array(
			'label'=>__("Auto delete history"),
			'type'=>'checkbox',
			'checkbox_fields' => array( 1 => __( 'Enable' ) ),
			'value' => 1,
			'field_name'=>'enable_history_auto_delete',
			'field_group'=>'advanced_field',
			'help_text'=>__('Enable auto delete for records within the history section.'),
			'validation_rule'=>array('type'=>'absint'),
			'form_toggler'=>array(
				'type'=>'parent',
				'target'=>'wt_pf_enable_history_auto_delete',
			),
		);

		$fields['auto_delete_history_count']=array(
			'label'=>__("Maximum entries"),
			'type'=>'number',
            'value' =>100,
            'attr' =>array(
            	'style'=>'width:30%;',
            ),
			'field_name'=>'auto_delete_history_count',
			'field_group'=>'advanced_field',
			'help_text'=>__('Indicates the maximum records to retain in history. Limit the number of records with status ‘Finished’. E.g On giving an input of 50, the system will retain(not delete) the latest 50 records with status ‘Finished’. Any other record with a different status will not be retained.'),
			'validation_rule'=>array('type'=>'absint'),
			'form_toggler'=>array(
				'type'=>'child',
				'id'=>'wt_pf_enable_history_auto_delete',
				'val'=>1,
				'chk' => 'true'		
			),
		);
		*/
		return $fields;
	}


	/** 
	*  History list page
	*/
	public function admin_settings_page($args)
	{
		global $wpdb;

		/* delete action */
		if(isset($_GET['wt_pf_delete_history'])) 
		{
			if(Wt_Pf_Sh::check_write_access(WEBTOFFEE_PRODUCT_FEED_ID))
			{
				$history_id_arr=isset($_GET['wt_pf_history_id']) ? explode(",", $_GET['wt_pf_history_id']) : array();
				$history_id_arr=Wt_Pf_Sh::sanitize_item($history_id_arr, 'absint_arr');
				if(count($history_id_arr)>0)
				{
					self::delete_history_by_id($history_id_arr);
					self::delete_cron_by_histoy_id($history_id_arr);
				}			
			}
		}

		/** 
		*	Lising page section 
		*/
		$tb=$wpdb->prefix.Webtoffee_Product_Feed_Sync::$history_tb;

		$post_type_arr=self::get_disticnt_items('item_type');
		$action_type_arr=self::get_disticnt_items('template_type');
		$status_arr=self::get_disticnt_items('status');

		$importer_post_types=apply_filters('wt_pf_importer_post_types_basic', array());
		$exporter_post_types=apply_filters('wt_pf_exporter_post_types_basic', array());
		$post_type_label_arr=array_merge($importer_post_types, $exporter_post_types);


		/**
		*	Get history entries by Schedule ID
		*/
		$cron_id=(isset($_GET['wt_pf_cron_id']) ? absint($_GET['wt_pf_cron_id']) : 0);
		$history_arr=array();
		$list_by_cron=false;
		if($cron_id>0)
		{
			$cron_module_obj=Webtoffee_Product_Feed_Sync::load_modules('cron');
			if(!is_null($cron_module_obj))
			{
				$cron_data=$cron_module_obj->get_cron_by_id($cron_id);
				if($cron_data)
				{
					$history_id_arr=($cron_data['history_id_list']!="" ? maybe_unserialize($cron_data['history_id_list']) : array());
					$history_id_arr=(is_array($history_id_arr) ? $history_id_arr : array());
					$list_by_cron=true;
				}else
				{
					$cron_id=0; //invalid cron id
				}
			}else
			{
				$cron_id=0; //cron module not enabled	
			}
		}


		/**
		 *	Filter by form fields
		 */ 
		$filter_by=array(
			'item_type'=>array(
				'label'=>__('Post type', 'webtoffee-product-feed'),
				'values'=>$post_type_arr,
				'val_labels'=>$post_type_label_arr,
				'val_type'=>'%s',
				'selected_val'=>'',
			),
			'template_type'=>array(
				'label'=>__('Action type', 'webtoffee-product-feed'),
				'values'=>$action_type_arr,
				'val_labels'=>self::$action_label_arr,
				'val_type'=>'%s',
				'selected_val'=>'',
			),
			'status'=>array(
				'label'=>__('Status', 'webtoffee-product-feed'),
				'values'=>$status_arr,
				'val_labels'=>self::$status_label_arr,
				'validation_rule'=>array('type'=>'absint'),
				'val_type'=>'%d',
				'selected_val'=>'',
			),
		);

		if($list_by_cron) // no need of these filters in `cron by` listing
		{
			unset($filter_by['item_type']);
			unset($filter_by['template_type']);
		}


		/**
		*	Order by field vals
		*/
		$order_by=array(
			'date_desc'=>array('label'=>__('Date descending', 'webtoffee-product-feed'), 'sql'=>'created_at DESC'),
			'date_asc'=>array('label'=>__('Date ascending', 'webtoffee-product-feed'), 'sql'=>'created_at ASC'),
		);

		/* just applying a text validation */
		$conf_arr=isset($_GET['wt_pf_history']) ? Wt_Pf_Sh::sanitize_item($_GET['wt_pf_history'], 'text_arr') : array();
		$url_params_allowed=array(); //this array will only include the allowed $_GET params. This will use in pagination section

		/**
		*	Filter by block
		*/
		$where_qry_val_arr=array(); //sql query WHERE clause val array
		$where_qry_format_arr=array(); //sql query  WHERE clause val format array
		if(isset($conf_arr['filter_by'])) /* filter by GET var exists */
		{		
			$url_params_allowed['filter_by']=array();/* for pagination purpose */

			$filter_by_conf=(is_array($conf_arr['filter_by']) ? $conf_arr['filter_by'] : array());
			$filter_by_validation_rule=Webtoffee_Product_Feed_Sync_Common_Helper::extract_validation_rules($filter_by);
			foreach ($filter_by as $filter_key => $filter_val)
			{
				if(isset($filter_by_conf[$filter_key]) && trim($filter_by_conf[$filter_key])!="") //current filter applied
				{
					$where_qry_format_arr[]=$filter_key.'='.$filter_val['val_type'];
					$filter_by[$filter_key]['selected_val']=Wt_Pf_Sh::sanitize_data($filter_by_conf[$filter_key], $filter_key, $filter_by_validation_rule);
					$where_qry_val_arr[]=$filter_by[$filter_key]['selected_val'];

					$url_params_allowed['filter_by'][$filter_key]=$filter_by[$filter_key]['selected_val']; /* for pagination purpose */
				}					
			}
		}

		/**
		*	Order by block
		*/
		$default_order_by=array_keys($order_by)[0];
		$order_by_val=$default_order_by;
		$order_qry_val_arr=array(); //sql query ORDER clause val array
		if(isset($conf_arr['order_by'])) /* order by GET var exists */
		{
			$order_by_val=(is_array($conf_arr['order_by']) ? $default_order_by : $conf_arr['order_by']);
		}
		if(isset($order_by[$order_by_val]))
		{
			$order_qry_val_arr[]=$order_by[$order_by_val]['sql'];
			$url_params_allowed['order_by']=$order_by_val; /* for pagination purpose */
		}

		/**
		*	Pagination block
		*/
		$max_data=(isset($conf_arr['max_data']) ? absint($conf_arr['max_data']) : $this->max_records);
		$this->max_records=($max_data>0 ? $max_data : $this->max_records);
		
		$offset=(isset($_GET['offset']) ? absint($_GET['offset']) : 0);
		$url_params_allowed['max_data']=$this->max_records;
		$pagination_url_params=array('wt_pf_history'=>$url_params_allowed, 'page'=>$this->module_id);
		$offset_qry_str=" LIMIT $offset, ".$this->max_records;
		$no_records=false;

		if($list_by_cron) /* list by cron */
		{
			$pagination_url_params['wt_pf_cron_id']=$cron_id; /* adding cron id to URL params */
			
			$total_history_ids=count($history_id_arr);
			if($total_history_ids>0)
			{
				$where_qry_format_arr[]='id IN('.implode(",", array_fill(0, $total_history_ids, "%d")).")";
				$where_qry_val_arr=array_merge($where_qry_val_arr, $history_id_arr);

			}else //reset all where, order by queries
			{
				$where_qry_format_arr=array();
				$where_qry_val_arr=array();
				//$order_qry_val_arr=array();
				$no_records=true;
			}			
		}

		$where_qry=(count($where_qry_format_arr)>0 ? ' WHERE '.implode(" AND ", $where_qry_format_arr) : '');
		$orderby_qry=(count($order_qry_val_arr)>0 ? ' ORDER BY '.implode(", ", $order_qry_val_arr) : '');

		/* prepare SQL */
		$num_sql="SELECT COUNT(id) AS total_records FROM $tb $where_qry";
		$list_sql="SELECT * FROM $tb $where_qry $orderby_qry ".$offset_qry_str;

		if(count($where_qry_format_arr)>0)
		{
			$num_sql=$wpdb->prepare($num_sql, $where_qry_val_arr);
			$list_sql=$wpdb->prepare($list_sql, $where_qry_val_arr);
		}

		if($no_records) /* in list_by cron, history IDs are not available */
		{
			$total_records=0;
			$history_list=array();
		}else
		{
			$total_records=$wpdb->get_row($num_sql, ARRAY_A);
			$total_records=($total_records && isset($total_records['total_records']) ? $total_records['total_records'] : 0);

			$history_list=$wpdb->get_results($list_sql, ARRAY_A);
			$history_list=($history_list ? $history_list : array());
		}

		$delete_url_params=$pagination_url_params;
		$delete_url_params['wt_pf_delete_history']=1;
		$delete_url_params['wt_pf_history_id']='_history_id_';
		$delete_url_params['offset']=$offset;
		$delete_url=wp_nonce_url(admin_url('admin.php?'.http_build_query($delete_url_params)), WEBTOFFEE_PRODUCT_FEED_ID);

		//enqueue script
		if(isset($_GET['page']) && $_GET['page']==$this->module_id)
		{
			$this->enqueue_scripts($delete_url);
		}


		include plugin_dir_path(__FILE__).'views/settings.php';
	}	

	private function enqueue_scripts($delete_url)
	{
            if(Webtoffee_Product_Feed_Sync_Common_Helper::wt_is_screen_allowed()){
		wp_enqueue_script($this->module_id, plugin_dir_url(__FILE__).'assets/js/main.js', array('jquery'), WEBTOFFEE_PRODUCT_FEED_SYNC_VERSION, false);

		$params=array(
			'delete_url'=>$delete_url,
			'msgs'=>array(
				'sure'=>__('Are you sure?', 'webtoffee-product-feed'),
			),
			'copied_msg' => __('URL copied to clipboard', 'webtoffee-product-feed')
		);
		wp_localize_script($this->module_id, 'wt_pf_history_basic_params', $params);
            }
	}

	public static function record_failure($history_id, $msg)
	{
		$update_data=array(
			'status'=>self::$status_arr['failed'],
			'status_text'=>$msg //no need to add translation function
		);
		$update_data_type=array(
			'%d',
			'%s',
		);
		self::update_history_entry($history_id, $update_data, $update_data_type);
	}

	/**
	*  Delete history entry from DB and also associated files (Export files only)
	*  @param array|int $id history entry IDs
	*/
	public static function delete_history_by_id($id)
	{
		global $wpdb;
		$tb=$wpdb->prefix.Webtoffee_Product_Feed_Sync::$history_tb;
		if(is_array($id))
		{
			$where=" IN(".implode(",", array_fill(0, count($id), '%d')).")";
			$where_data=$id;
		}else
		{
			$where="=%d";
			$where_data=array($id);
		}

		//first remove files associated with it. give argument as array then no need to check the result array type
		$allowed_ext_arr=array('csv', 'xml'); //please update this array if new file types introduced
		$list=self::get_history_entry_by_id($where_data);
		if($list)
		{
			foreach($list as $listv)
			{
				if($listv['template_type']=='export') //history is for export action
				{
					if(Webtoffee_Product_Feed_Sync_Admin::module_exists('export'))
					{
						$ext_arr=explode('.', $listv['file_name']);
						$ext=end($ext_arr);
						if(in_array($ext, $allowed_ext_arr)) /* delete only allowed extensions */
						{
							$file_path=Webtoffee_Product_Feed_Sync_Export::get_file_path($listv['file_name']);
							if($file_path && file_exists($file_path))
							{
								@unlink($file_path);
							}
						}					
					}
				}elseif($listv['template_type']=='import')
				{
					$action_module_obj=Webtoffee_Product_Feed_Sync::load_modules('import');

					$log_file_name=$action_module_obj->get_log_file_name($listv['id']);
					$log_file_path=$action_module_obj->get_file_path($log_file_name);
					if(file_exists($log_file_path))
					{
						@unlink($log_file_path);
					}
				}
			}	
		}


		$wpdb->query( 
		    $wpdb->prepare("DELETE FROM $tb WHERE id".$where, $where_data)
		);
	}

	/**
	*  Delete cron entry from DB
	*/
	public static function delete_cron_by_histoy_id($id)
	{
		global $wpdb;
		$tb=$wpdb->prefix.Webtoffee_Product_Feed_Sync::$cron_tb;
		if(is_array($id))
		{
			$where=" IN(".implode(",", array_fill(0, count($id), '%d')).")";
			$where_data=$id;
		}else
		{
			$where="=%d";
			$where_data=array($id);
		}

		$wpdb->query( 
		    $wpdb->prepare("DELETE FROM $tb WHERE history_id".$where, $where_data)
		);
	}	
	
	public static function update_history_entry($history_id, $update_data, $update_data_type)
	{
		global $wpdb;
		//updating the data
		$tb=$wpdb->prefix.Webtoffee_Product_Feed_Sync::$history_tb;
		$update_where=array(
			'id'=>$history_id
		);
		$update_where_type=array(
			'%d'
		);
		if($wpdb->update($tb, $update_data, $update_where, $update_data_type, $update_where_type)!==false)
		{
			return true;
		}
		return false;
	}

	/**
	*	Mathod perform actions after advanced settings was updated
	*
	*/
	public function after_advanced_setting_update($advanced_settings)
	{
		/* Check auto deletion enabled */
		if(isset($advanced_settings['wt_pf_enable_history_auto_delete']) && $advanced_settings['wt_pf_enable_history_auto_delete']==1)
		{
			$record_count=(isset($advanced_settings['wt_pf_auto_delete_history_count']) ? absint($advanced_settings['wt_pf_auto_delete_history_count']) : 0);
			if($record_count>0)
			{
				self::auto_delete_history_entry($record_count);
			}
		}
	}

	/**
	*	Check and delete history entry. If auto deletion enabled
	*
	*/
	public static function auto_delete_history_entry($record_count=0)
	{
		if($record_count==0) /* this condition is for, some requests will come from create section or some from advanced settings section */
		{
			if(Webtoffee_Product_Feed_Sync_Common_Helper::get_advanced_settings('enable_history_auto_delete')==1)
			{
			 	$record_count=absint(Webtoffee_Product_Feed_Sync_Common_Helper::get_advanced_settings('auto_delete_history_count'));	 	
			}
		}
		if($record_count>=1)
	 	{
	 		global $wpdb;
			$tb=$wpdb->prefix.Webtoffee_Product_Feed_Sync::$history_tb;
			
			$data=$wpdb->get_results("SELECT * FROM $tb WHERE status=".self::$status_arr['finished']." AND id<(SELECT id FROM $tb ORDER BY id DESC LIMIT ".($record_count-1).",1)", ARRAY_A);
			
			if($data && is_array($data))
			{
				$id_arr=array_column($data, 'id');
				self::delete_history_by_id($id_arr);
			}
	 	}
	}

	/**
	* 	create a history entry before starting export/import
	*	@param 	$file_name String export/import file name
	*	@param 	$form_data Array export/import formdata
	*	@param 	$action String export or import
	*	@return $history_id Int DB id if success otherwise zero
	*/
	public static function create_history_entry($file_name, $form_data, $to_process, $action)
	{
		global $wpdb;

		$tb=$wpdb->prefix.Webtoffee_Product_Feed_Sync::$history_tb;
		$insert_data=array(
			'template_type'=>$action,
			'item_type'=>$to_process, //item type Eg: product
			'file_name'=>$file_name, //export/import file name
			'created_at'=>time(), //craeted time
			'updated_at'=>time(), //craeted time
			'data'=>maybe_serialize($form_data), //formadata
			'status'=>self::$status_arr['pending'], //pending
			'status_text'=>'Pending', //pending, No need to add translate function. we can add this on printing page
			'offset'=>0, //current offset, its always 0 on start
			'total'=>0, //total records, not available now
		);
		$insert_data_type=array(
			'%s','%s','%s','%d','%d','%s','%d','%d','%d'
		);
		
		$insert_response=$wpdb->insert($tb, $insert_data, $insert_data_type);
		
		/* check for auto delete */
		self::auto_delete_history_entry();

		if($insert_response) //success
		{
			return $wpdb->insert_id;
		}
		return 0;
	}

	/**
	* 	Get distinct column values from history table
	*	@param string $column table column name
	*	@return array array of distinct column values
	*/
	private static function get_disticnt_items($column)
	{
		global $wpdb;
		$tb=$wpdb->prefix.Webtoffee_Product_Feed_Sync::$history_tb;
		$data=$wpdb->get_results("SELECT DISTINCT $column FROM $tb ORDER BY $column ASC", ARRAY_A);
		$data=is_array($data) ? $data : array();
		return array_column($data, $column);
	}
	

	public static function get_filename_items()
	{
		global $wpdb;
		$tb=$wpdb->prefix.Webtoffee_Product_Feed_Sync::$history_tb;
		$data=$wpdb->get_results("SELECT file_name FROM $tb" , ARRAY_A);
		$data=is_array($data) ? $data : array();
		return $data;
	}

	/**
	* 	Taking history entry by ID
	*/
	public static function get_history_entry_by_id($id)
	{
		global $wpdb;
		$tb=$wpdb->prefix.Webtoffee_Product_Feed_Sync::$history_tb;
		if(is_array($id))
		{
			$where=" IN(".implode(",", array_fill(0, count($id), '%d')).")";
			$where_data=$id;
		}else
		{
			$where="=%d";
			$where_data=array($id);
		}
		$qry=$wpdb->prepare("SELECT * FROM $tb WHERE id".$where, $where_data);

		if(!is_array($id))
		{
			return $wpdb->get_row($qry, ARRAY_A);
		}else
		{
			return $wpdb->get_results($qry, ARRAY_A);
		} 
	}


	/**
	*  	Download log file via a nonce URL
	*/
	public function download_file()
	{
		if(isset($_GET['wt_pf_log_download']))
		{ 
			if(Wt_Pf_Sh::check_write_access(WEBTOFFEE_PRODUCT_FEED_ID)) /* check nonce and role */
			{
				$file_name=(isset($_GET['file']) ? sanitize_text_field($_GET['file']) : '');
				if($file_name!="")
				{
					$file_arr=explode(".", $file_name);
					$file_ext=end($file_arr);
					if($file_ext=='log') /* Only allowed files. */
					{
						$file_path= '';//Webtoffee_Product_Feed_Sync_Basic_Log::get_file_path($file_name);
						if(file_exists($file_path) && is_file($file_path))
						{	
							header('Pragma: public');
						    header('Expires: 0');
						    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
						    header('Cache-Control: private', false);
						    header('Content-Transfer-Encoding: binary');
						    header('Content-Disposition: attachment; filename="'.$file_name.'";');
						    header('Content-Description: File Transfer');
						    header('Content-Type: application/octet-stream');
						    //header('Content-Length: '.filesize($file_path));

						    $chunk_size=1024 * 1024;
						    $handle=@fopen($file_path, 'rb');
						    while(!feof($handle))
						    {
						        $buffer = fread($handle, $chunk_size);
						        echo $buffer;
						        ob_flush();
						        flush();
						    }
						    fclose($handle);
						    exit();

						}
					}
				}	
			}
		}
	}


	/**
	* 
	* 	Generate pagination HTML
    */
	public static function gen_pagination_html($total, $limit, $offset, $url, $url_params=array(), $mxnav=6)
	{
		if($total<=0){
 			return '';
 		}
 		/* taking current page */
 		$crpage=($offset+$limit)/$limit;

		$limit=$limit<=0 ? 1 : $limit;
 		$ttpg=ceil($total/$limit);
 		if($ttpg<$crpage){
 			//return '';
 		}

 		//calculations
		$mxnav=$ttpg<$mxnav ? $ttpg : $mxnav;

		$mxnav_mid=floor($mxnav/2);
		$pgstart=$mxnav_mid>=$crpage ? 1 : $crpage-$mxnav_mid; 
		$mxnav_mid+=$mxnav_mid>=$crpage ? ($mxnav_mid-$crpage) : 0;  //adjusting other half with first half balance
		$pgend=$crpage+$mxnav_mid;
		if($pgend>$ttpg)
		{
			$pgend=$ttpg;
		}

		$html='<span class="wt_pf_pagination_total_info">'.$total.__(' record(s)').'</span>';
		$url_params_string=http_build_query($url_params);
		$url_params_string=$url_params_string!="" ? '&'.$url_params_string : '';
		$url=(strpos($url, '?')!==false ? $url.'&' : $url.'?');
		$href_attr=' href="'.$url.'offset={offset}'.$url_params_string.'"';
		
		$prev_onclick='';
		if($crpage>1)
		{
			$offset=(($crpage-2)*$limit);
			$prev_onclick=str_replace('{offset}', $offset, $href_attr);
		}

		$html.='<a class="'.($crpage>1 ? 'wt_pf_page' : 'wt_pf_pagedisabled').'"'.$prev_onclick.'>‹</a>';
		for($i=$pgstart; $i<=$pgend; $i++)
		{
			$page_offset='';
			$onclick='';
			$offset=($i*$limit)-$limit;
			if($i!=$crpage)
			{
				$onclick=str_replace('{offset}', $offset, $href_attr);
			}			
			$html.='<a class="'.($i==$crpage ? 'wt_pf_pageactive' : 'wt_pf_page').'" '.$onclick.'>'.$i.'</a>';
		}

		$next_onclick='';
		if($crpage<$ttpg)
		{
			$offset=($crpage*$limit);
			$next_onclick=str_replace('{offset}', $offset, $href_attr);
		}

		$html.='<a class="'.($crpage<$ttpg ? 'wt_pf_page' : 'wt_pf_pagedisabled').'"'.$next_onclick.'>›</a>';
		return '<div class="wt_pf_pagination"><span>'.$html.'</div>';
	}
}
}
Webtoffee_Product_Feed_Sync::$loaded_modules['history']=new Webtoffee_Product_Feed_Sync_History();