<?php
class Transaction extends Zend_Db_Table 
{
	protected $getVal;
	public function init() {
		$this->settings    = new Settings();

		if(Zend_Session::namespaceIsset('sess_remote_database')) {
			$remoteSession = new Zend_Session_Namespace('sess_remote_database');
			$this->remoteDb = new Zend_Db_Adapter_Pdo_Mysql(array(
							    'host'     =>  $remoteSession->hostName,
							    'username' =>  $remoteSession->userName,
						        'password' =>  $remoteSession->password,
								'dbname'   =>  $remoteSession->dataBase
								)); 

			$authAdapter = new Zend_Auth_Adapter_DbTable($this->remoteDb);
		}
		//echo '<pre>'; print_r($this->remoteDb); echo '</pre>';
	}

	/**
	* Purpose  get income transaction details for the particular company database
	* @param   none
	* @return  all income transaction details
	*/	

	public function getIncomeTransaction($id='',$sort='') {
		if(isset($id) && !empty($id)) {
			$where = 't1.id='.$id.'';
			$select  = $this->remoteDb->select()
					 ->from(array('t1' => 'income_transaction'))/*
					 ->joinLeft(array('t2' => 'account'),'t1.fkpayment_account = t2.id',array('t2.id as aid',
					 		't2.account_type','t2.account_name'))*/
					 ->joinLeft(array('t3' => 'customers'),'t1.fkcustomer_id = t3.id',array('t3.id as cid',
					 		't3.customer_id','t3.customer_name','t3.coa_link','t3.other_coa_link'))
					 ->joinLeft(array('t4' => 'taxcodes'),'t1.fktax_id = t4.id',array('t4.id as tid',
					 		't4.tax_code','t4.description as tax_description'))
					 ->where($where);
		    $sql = $this->remoteDb->fetchAll($select);
			return $sql;			
		} else {
			$order = 't1.id DESC';
			$where = '1 AND t1.delete_status=1';
			if(isset($sort) && !empty($sort)) {
				$where = 't1.transaction_status = '.$sort.' AND t1.delete_status=1';
			}
			$select  = $this->remoteDb->select()
					 ->from(array('t1' => 'income_transaction'))/*
					 ->joinLeft(array('t2' => 'account'),'t1.fkpayment_account = t2.id',array('t2.id as aid',
					 		't2.account_type','t2.account_name'))*/
					 ->joinLeft(array('t3' => 'customers'),'t1.fkcustomer_id = t3.id',array('t3.id as cid',
					 		't3.customer_id','t3.customer_name','t3.coa_link','t3.other_coa_link'))
					 ->joinLeft(array('t4' => 'taxcodes'),'t1.fktax_id = t4.id',array('t4.id as tid',
					 		't4.tax_code','t4.description as tax_description'))
					 ->where($where)
					 ->order($order);
			//$sql = $select->__toString();
		//	echo "$sql\n";	die();
		    $sql = $this->remoteDb->fetchAll($select);
			return $sql;
		}
	}




	public function getIncomeAuditTransaction($id='',$sort='') {
		if(isset($id) && !empty($id)) {
			$where = 't1.id='.$id.'';
			$select  = $this->remoteDb->select()
					 ->from(array('t1' => 'income_transaction_audit'))/*
					 ->joinLeft(array('t2' => 'account'),'t1.fkpayment_account = t2.id',array('t2.id as aid',
					 		't2.account_type','t2.account_name'))*/
					 ->joinLeft(array('t3' => 'customers'),'t1.fkcustomer_id = t3.id',array('t3.id as cid',
					 		't3.customer_id','t3.customer_name','t3.coa_link','t3.other_coa_link'))
					 ->joinLeft(array('t4' => 'taxcodes'),'t1.fktax_id = t4.id',array('t4.id as tid',
					 		't4.tax_code','t4.description as tax_description'))
					 ->where($where);
		    $sql = $this->remoteDb->fetchAll($select);
			return $sql;			
		} else {
			return false;
		}
	}


	/**
	* Purpose  get expense transaction details for the particular company database
	* @param   none
	* @return  all expense transaction details
	*/	

	public function getExpenseTransaction($id='',$sort='') {
		if(isset($id) && !empty($id)) {
			$where = 't1.id='.$id.'';
			$select  = $this->remoteDb->select()
					 ->from(array('t1' => 'expense_transaction'))
					 ->joinLeft(array('t2' => 'vendors'),'t1.fkvendor_id = t2.id',array('t2.id as vid',
					 		't2.vendor_id','t2.vendor_name','t2.coa_link','t2.other_coa_link'))/*
					 ->joinLeft(array('t3' => 'account'),'t1.fkpayment_account = t3.id',array('t3.id as aid',
					 		't3.account_type','t3.account_name'))*/
					 ->where($where);
		    $sql = $this->remoteDb->fetchAll($select);
			return $sql;			
		} else {
			$where = '1 AND t1.delete_status=1';
			if(isset($sort) && !empty($sort)) {
				$where = 't1.transaction_status = '.$sort.' AND t1.delete_status=1';
			}
			$select  = $this->remoteDb->select()
					 ->from(array('t1' => 'expense_transaction'))
					 ->joinLeft(array('t2' => 'vendors'),'t1.fkvendor_id = t2.id',array('t2.id as vid',
					 		't2.vendor_id','t2.vendor_name','t2.coa_link','t2.other_coa_link'))
					 ->joinLeft(array('t3' => 'expense_transaction_list'),'t3.fkexpense_id = t1.id',array('t3.id as eid',"amount" => "sum(t3.unit_price * t3.quantity)","tax_amount" => "sum((t3.unit_price * t3.quantity) * t3.tax_value / 100)"))
					 /*->joinLeft(array('t4' => 'account'),'t1.fkpayment_account = t4.id',array('t4.id as aid',
					 		't4.account_type','t4.account_name'))*/
					 ->where($where)
					 ->group('t1.id');

		    $sql = $this->remoteDb->fetchAll($select);
			return $sql;
		}
	}




	public function getExpenseAuditTransaction($id='',$sort='') {
		if(isset($id) && !empty($id)) {
			$where = 't1.id='.$id.'';
			$select  = $this->remoteDb->select()
					 ->from(array('t1' => 'expense_transaction_audit'))
					 ->joinLeft(array('t2' => 'vendors'),'t1.fkvendor_id = t2.id',array('t2.id as vid',
					 		't2.vendor_id','t2.vendor_name','t2.coa_link','t2.other_coa_link'))/*
					 ->joinLeft(array('t3' => 'account'),'t1.fkpayment_account = t3.id',array('t3.id as aid',
					 		't3.account_type','t3.account_name'))*/
					 ->where($where);
		    $sql = $this->remoteDb->fetchAll($select);
			return $sql;			
		} else {
			return false;
		}
	}

	/**
	* Purpose  get maximum expense transaction for each individual expense expense
	* @param   none
	* @return  all maximum expense transaction details
	*/	

	public function getMaxExpenseTransaction() {
		$select  = $this->remoteDb->select()
					 ->from(array('t1' => 'expense_transaction_list'),array('t1.fkexpense_id','t1.fkexpense_type','t1.product_description',"maxi"=>"MAX(t1.unit_price * t1.quantity)"))
					 ->joinLeft(array('t2' => 'account'),'t1.fkexpense_type = t2.id',array('t2.id as aid',
					 		't2.account_name'))
					 ->group('t1.id')
					 ->order('maxi DESC');
		$sql = $this->remoteDb->fetchAll($select);
	    return $sql;
	}


	/**
	* Purpose  get expense transaction list of details for the particular company database
	* @param   none
	* @return  all expense transaction list details
	*/	

	public function getExpenseTransactionList($id) {
		if(isset($id) && !empty($id)) {
			$where = 't1.fkexpense_id='.$id.'';
			$select  = $this->remoteDb->select()
					 ->from(array('t1' => 'expense_transaction_list'))
					 ->joinLeft(array('t2' => 'account'),'t1.fkexpense_type = t2.id',array('t2.id as aid','t2.account_name'))
					 ->joinLeft(array('t3' => 'taxcodes'),'t1.fktax_id = t3.id',array('t3.id as tid',
					 		't3.tax_code','t3.tax_percentage'))
					 ->where($where);
		    $sql = $this->remoteDb->fetchAll($select);
			return $sql;			
		}   
	}


	public function getExpenseAuditTransactionList($id) {
		if(isset($id) && !empty($id)) {
			$where = 't1.fkexpense_id='.$id.'';
			$select  = $this->remoteDb->select()
					 ->from(array('t1' => 'expense_transaction_list_audit'))
					 ->joinLeft(array('t2' => 'account'),'t1.fkexpense_type = t2.id',array('t2.id as aid','t2.account_name'))
					 ->joinLeft(array('t3' => 'taxcodes'),'t1.fktax_id = t3.id',array('t3.id as tid',
					 		't3.tax_code','t3.tax_percentage'))
					 ->where($where);
		    $sql = $this->remoteDb->fetchAll($select);
			return $sql;			
		}   
	}


	/**
	* Purpose  get invoice details for the particular company database
	* @param   none
	* @return  all invoice details
	*/	

	public function getInvoiceTransaction($id='',$sort='') {
		if(isset($id) && !empty($id)) {
			$where = 't1.id='.$id.'';
			$select  = $this->remoteDb->select()
					 ->from(array('t1' => 'invoice'))
					 ->joinLeft(array('t2' => 'customers'),'t1.fkcustomer_id = t2.id',array('t2.id as cid',
					 			't2.customer_id','t2.customer_name','t2.coa_link','t2.other_coa_link','t2.address1','t2.city','t2.state','t2.country','t2.office_number','t2.postcode'))
					 ->joinLeft(array('t3' => 'customer_shipping_address'),'t1.fkshipping_address = t3.id',array('t3.id as sid',
					 			't3.shipping_address1'))
					 ->where($where);
		    $sql = $this->remoteDb->fetchAll($select);
			return $sql;	
		} else {
			$where = '1 AND t1.delete_status=1';
			if(isset($sort) && !empty($sort)) {
				$where = 't1.invoice_status = '.$sort.' AND t1.delete_status=1';
			}
			$select  = $this->remoteDb->select()
					 ->from(array('t1' => 'invoice'))
					 ->joinLeft(array('t2' => 'customers'),'t1.fkcustomer_id = t2.id',array('t2.id as cid',
					 			't2.customer_id','t2.customer_name','t2.coa_link','t2.other_coa_link'))
					 ->joinLeft(array('t3' => 'invoice_product_list'),'t3.fkinvoice_id = t1.id',array('t3.id as pid',"amount" => "sum(t3.unit_price * t3.quantity - t3.discount_amount)","tax_amount" => "sum((t3.unit_price * t3.quantity - t3.discount_amount) * t3.tax_value / 100)"))
 					 //->joinLeft(array('t4' => 'payments'),('t4.fkiei_id = t1.id'),array('t4.id as paid',"pay_amount" => "sum(t4.payment_amount)"))
					 ->where($where)
					 ->group('t1.id');
			//$sql = $select->__toString();
			//echo "$sql\n";
		    $sql = $this->remoteDb->fetchAll($select);
			return $sql;
		}
	}



	public function getInvoiceAuditTransaction($id='',$sort='') {
		if(isset($id) && !empty($id)) {
			$where = 't1.id='.$id.'';
			$select  = $this->remoteDb->select()
					 ->from(array('t1' => 'invoice_audit'))
					 ->joinLeft(array('t2' => 'customers'),'t1.fkcustomer_id = t2.id',array('t2.id as cid',
					 			't2.customer_id','t2.customer_name','t2.coa_link','t2.other_coa_link','t2.address1','t2.city','t2.state','t2.country','t2.office_number','t2.postcode'))
					 ->joinLeft(array('t3' => 'customer_shipping_address'),'t1.fkshipping_address = t3.id',array('t3.id as sid',
					 			't3.shipping_address1'))
					 ->where($where);
		    $sql = $this->remoteDb->fetchAll($select);
			return $sql;	
		} else {
			return false;
		}
	}

	/**
	* Purpose  get invoice credit note details for the particular company database
	* @param   none
	* @return  all invoice credit note details
	*/	

	public function getInvoiceCreditTransaction() {
		    $where = 't1.invoice_status!=3';
			$select  = $this->remoteDb->select()
					 ->from(array('t1' => 'invoice'))
					 ->joinLeft(array('t2' => 'customers'),'t1.fkcustomer_id = t2.id',array('t2.id as cid',
					 			't2.customer_id','t2.customer_name'))
					 ->joinLeft(array('t3' => 'invoice_product_list'),'t3.fkinvoice_id = t1.id',array('t3.id as pid',"amount" => "sum(t3.unit_price * t3.quantity - t3.discount_amount)","tax_amount" => "sum((t3.unit_price * t3.quantity - t3.discount_amount) * t3.tax_value / 100)"))
 					 //->joinLeft(array('t4' => 'payments'),('t4.fkiei_id = t1.id'),array('t4.id as paid',"pay_amount" => "sum(t4.payment_amount)"))
					 ->where($where)
					 ->group('t1.id');
			//$sql = $select->__toString();
			//echo "$sql\n";	
		    $sql = $this->remoteDb->fetchAll($select);
			return $sql;
	}


	/**
	* Purpose  get credit note details for the particular company database
	* @param   none
	* @return  all credit note details
	*/	

	public function getCreditTransaction($id='',$sort='') {
		if(isset($id) && !empty($id)) {
			$where = 't1.id='.$id.'';
			$select  = $this->remoteDb->select()
					 ->from(array('t1' => 'credit'))
					 ->joinLeft(array('t2' => 'customers'),'t1.fkcustomer_id = t2.id',array('t2.id as cid',
					 			't2.customer_id','t2.customer_name','t2.coa_link','t2.other_coa_link','t2.address1','t2.city','t2.state','t2.country','t2.office_number','t2.postcode'))
					 ->joinLeft(array('t4' => 'invoice'),('t4.id = t1.fkinvoice_id'),array('t4.id as invid','t4.invoice_no','t4.date as inv_date'))
					 ->where($where);
		    $sql = $this->remoteDb->fetchAll($select);
			return $sql;			
		} else {
			$where = '1 AND t1.delete_status=1';
			if(isset($sort) && !empty($sort)) {
				$where = 't1.credit_status = '.$sort.' AND t1.delete_status=1';
			}
			$select  = $this->remoteDb->select()
					 ->from(array('t1' => 'credit'))
					 ->joinLeft(array('t2' => 'customers'),'t1.fkcustomer_id = t2.id',array('t2.id as cid',
					 			't2.customer_id','t2.customer_name'))
					 ->joinLeft(array('t3' => 'credit_product_list'),'t3.fkcredit_id = t1.id',array('t3.id as pid',"amount" => "sum(t3.unit_price * t3.quantity - t3.discount_amount)","tax_amount" => "sum((t3.unit_price * t3.quantity - t3.discount_amount) * t3.tax_value / 100)"))
 					 ->joinLeft(array('t4' => 'invoice'),('t4.id = t1.fkinvoice_id'),array('t4.id as invid','t4.invoice_no'))
					 ->where($where)
					 ->group('t1.id');
			//$sql = $select->__toString();
			//echo "$sql\n";	
		    $sql = $this->remoteDb->fetchAll($select);
			return $sql;
		}
	}



	public function getCreditAuditTransaction($id='',$sort='') {
		if(isset($id) && !empty($id)) {
			$where = 't1.id='.$id.'';
			$select  = $this->remoteDb->select()
					 ->from(array('t1' => 'credit_audit'))
					 ->joinLeft(array('t2' => 'customers'),'t1.fkcustomer_id = t2.id',array('t2.id as cid',
					 			't2.customer_id','t2.customer_name','t2.coa_link','t2.other_coa_link','t2.address1','t2.city','t2.state','t2.country','t2.office_number','t2.postcode'))
					 ->joinLeft(array('t4' => 'invoice'),('t4.id = t1.fkinvoice_id'),array('t4.id as invid','t4.invoice_no','t4.date as inv_date'))
					 ->where($where);
		    $sql = $this->remoteDb->fetchAll($select);
			return $sql;			
		} else {
			return false;
		}
	}

	/**
	* Purpose  get Credit note details for particular invoice for the particular company database
	* @param   invoice id
	* @return  all credit note details for particular invoice
	*/	

	public function getInvoiceCredit($id='') {
		if(isset($id) && !empty($id)) {
			$where   = 't1.fkinvoice_id='.$id.' AND t1.delete_status=1';  
			$select  = $this->remoteDb->select()
					 ->from(array('t1' => 'credit'),array('t1.id','t1.credit_no','t1.fkinvoice_id','t1.date','t1.memo','t1.transaction_currency','t1.exchange_rate'))
					 ->joinLeft(array('t3' => 'credit_product_list'),'t3.fkcredit_id = t1.id',array('t3.id as pid',"amount" => "sum(t3.unit_price * t3.quantity - t3.discount_amount)","tax_amount" => "sum((t3.unit_price * t3.quantity - t3.discount_amount) * t3.tax_value / 100)"))
 					 ->joinLeft(array('t4' => 'invoice'),('t4.id = t1.fkinvoice_id'),array('t4.id as invid','t4.invoice_no'))
					 ->where($where)
					 ->group('t1.id');
		    $sql = $this->remoteDb->fetchAll($select);
			return $sql;
		} else {
			$where = 't1.delete_status=1';
			$select  = $this->remoteDb->select()
					 ->from(array('t1' => 'credit'),array('t1.id','t1.credit_no','t1.fkinvoice_id','t1.transaction_currency','t1.exchange_rate'))
					 ->joinLeft(array('t3' => 'credit_product_list'),'t3.fkcredit_id = t1.id',array('t3.id as pid',"amount" => "sum(t3.unit_price * t3.quantity - t3.discount_amount)","tax_amount" => "sum((t3.unit_price * t3.quantity - t3.discount_amount) * t3.tax_value / 100)"))
 					 ->joinLeft(array('t4' => 'invoice'),('t4.id = t1.fkinvoice_id'),array('t4.id as invid','t4.invoice_no'))
					 ->where($where)
					 ->group('t1.fkinvoice_id');
		    $sql = $this->remoteDb->fetchAll($select);
			return $sql;		
		}
	}

	/**
	* Purpose  get journal entry details for the particular company database
	* @param   none
	* @return  all journal entry details
	*/	

	public function getJournalTransaction($id='',$sort='') {
		if(isset($id) && !empty($id)) {
			$where = 't1.id='.$id.'';
			$select  = $this->remoteDb->select()
					 ->from(array('t1' => 'journal_entries'))
					 ->where($where);
		    $sql = $this->remoteDb->fetchAll($select);
			return $sql;			
		} else {
			$where = '1 AND t1.delete_status=1';
			if(isset($sort) && !empty($sort)) {
				$where = 't1.journal_status = '.$sort.' AND t1.delete_status=1';
			}
			$select  = $this->remoteDb->select()
					 ->from(array('t1' => 'journal_entries'))
					 ->joinLeft(array('t3' => 'journal_entries_list'),'t3.fkjournal_id = t1.id',array('t3.id as jid','t3.journal_description',"total_debit" => "sum(t3.debit)","total_credit" => "sum(t3.credit)"))
					 ->where($where)
					 ->group('t3.fkjournal_id');
			//$sql = $select->__toString();
			//echo "$sql\n";	
		    $sql = $this->remoteDb->fetchAll($select);
			return $sql;
		}
	}


	public function getJournalAuditTransaction($id='',$sort='') {
		if(isset($id) && !empty($id)) {
			$where = 't1.id='.$id.'';
			$select  = $this->remoteDb->select()
					 ->from(array('t1' => 'journal_entries_audit'))
					 ->where($where);
		    $sql = $this->remoteDb->fetchAll($select);
			return $sql;			
		} else {
			return false;
		}
	}


	/**
	* Purpose  get invoice product list of details for the particular company database
	* @param   none
	* @return  all invoice product list details
	*/	

	public function getInvoiceProductList($id) {
		if(isset($id) && !empty($id)) {
			$where = 't1.fkinvoice_id='.$id.'';
			$select  = $this->remoteDb->select()
					 ->from(array('t1' => 'invoice_product_list'))
					 ->joinLeft(array('t2' => 'products'),'t1.product_description = t2.id',array('t2.id as pid',
					 		't2.product_id','t2.description','t2.price'))
					 ->joinLeft(array('t3' => 'taxcodes'),'t1.fktax_id = t3.id',array('t3.id as tid',
					 		't3.tax_code','t3.tax_percentage'))
					 ->where($where);
		    $sql = $this->remoteDb->fetchAll($select);
			return $sql;			
		}   
	}


	public function getInvoiceAuditProductList($id) {
		if(isset($id) && !empty($id)) {
			$where = 't1.fkinvoice_id='.$id.'';
			$select  = $this->remoteDb->select()
					 ->from(array('t1' => 'invoice_product_list_audit'))
					 ->joinLeft(array('t2' => 'products'),'t1.product_description = t2.id',array('t2.id as pid',
					 		't2.product_id','t2.description','t2.price'))
					 ->joinLeft(array('t3' => 'taxcodes'),'t1.fktax_id = t3.id',array('t3.id as tid',
					 		't3.tax_code','t3.tax_percentage'))
					 ->where($where);
		    $sql = $this->remoteDb->fetchAll($select);
			return $sql;			
		}   
	}


	/**
	* Purpose  get credit note product list of details for the particular company database
	* @param   none
	* @return  all credit product list details
	*/	

	public function getCreditProductList($id) {
		if(isset($id) && !empty($id)) {
			$where = 't1.fkcredit_id='.$id.'';
			$select  = $this->remoteDb->select()
					 ->from(array('t1' => 'credit_product_list'))
					 ->joinLeft(array('t2' => 'products'),'t1.product_description = t2.id',array('t2.id as pid',
					 		't2.product_id','t2.description','t2.price'))
					 ->joinLeft(array('t3' => 'taxcodes'),'t1.fktax_id = t3.id',array('t3.id as tid',
					 		't3.tax_code','t3.tax_percentage'))
					 ->where($where);
		    $sql = $this->remoteDb->fetchAll($select);
			return $sql;			
		}   
	}


	public function getCreditAuditProductList($id) {
		if(isset($id) && !empty($id)) {
			$where = 't1.fkcredit_id='.$id.'';
			$select  = $this->remoteDb->select()
					 ->from(array('t1' => 'credit_product_list_audit'))
					 ->joinLeft(array('t2' => 'products'),'t1.product_description = t2.id',array('t2.id as pid',
					 		't2.product_id','t2.description','t2.price'))
					 ->joinLeft(array('t3' => 'taxcodes'),'t1.fktax_id = t3.id',array('t3.id as tid',
					 		't3.tax_code','t3.tax_percentage'))
					 ->where($where);
		    $sql = $this->remoteDb->fetchAll($select);
			return $sql;			
		}   
	}


	/**
	* Purpose  get journal entry list of details for the particular company database
	* @param   none
	* @return  alljournal entry listt details
	*/	

	public function getJournalEntryList($id) {
		if(isset($id) && !empty($id)) {
			$where = 't1.fkjournal_id='.$id.'';
			$select  = $this->remoteDb->select()
					 ->from(array('t1' => 'journal_entries_list'))
					 ->joinLeft(array('t2' => 'account'),'t1.fkaccount_id = t2.id',array('t2.id as aid',
					 		't2.account_name'))
					 ->where($where);
		    $sql = $this->remoteDb->fetchAll($select);
			return $sql;			
		}   
	}


	public function getJournalAuditEntryList($id) {
		if(isset($id) && !empty($id)) {
			$where = 't1.fkjournal_id='.$id.'';
			$select  = $this->remoteDb->select()
					 ->from(array('t1' => 'journal_entries_list_audit'))
					 ->joinLeft(array('t2' => 'account'),'t1.fkaccount_id = t2.id',array('t2.id as aid',
					 		't2.account_name'))
					 ->where($where);
		    $sql = $this->remoteDb->fetchAll($select);
			return $sql;			
		}   
	}

	/**
	* Purpose  get particular invoice payment details for the particular company database
	* @param   none
	* @return  invoice payment details
	*/	

	public function getPaymentDetails($id='',$status) {
		if(isset($id) && !empty($id)) {
			$where = 't1.fkiei_id='.$id.' AND t1.payment_status='.$status.'';
			$select  = $this->remoteDb->select()
					 ->from(array('t1' => 'payments'))
					 ->where($where);
		    $sql = $this->remoteDb->fetchAll($select);
			return $sql;			
		}   else {
			$sql = $this->remoteDb->fetchAll('SELECT id,payment_amount,discount_amount,fkiei_id FROM payments WHERE payment_status = '.$status.'');
			return $sql;
		}
	}


	public function getPaymentAudit($id) {
		if(isset($id) && !empty($id)) {
			$where = 't1.id='.$id.'';
			$select  = $this->remoteDb->select()
					 ->from(array('t1' => 'payments_audit'))
					 ->where($where);
		    $sql = $this->remoteDb->fetchAll($select);
			return $sql;			
		}   else {
			return false;
		}
	}

	/**
	* Purpose  get all customer details for the particular company database
	* @param   none
	* @return  customer id and name
	*/	

	public function getCustomerDetails() {
		$sql = $this->remoteDb->fetchAll('SELECT id,customer_id,customer_name,coa_link,other_coa_link FROM customers WHERE delete_status=1 ORDER BY customer_name ASC');
		return $sql;
	}

	/**
	* Purpose  get all customer shipping details for the particular company database
	* @param   none
	* @return  all shipping addresses
	*/	

	public function getShippingDetails() {
		$sql = $this->remoteDb->fetchAll('SELECT * FROM customer_shipping_address');
		return $sql;
	}

	/**
	* Purpose  get  customer shipping details under particular customer for the particular company database
	* @param   customer id
	* @return  shipping addresses
	*/	

	public function getCustomerShippingDetails($id) {
		$sql = $this->remoteDb->fetchAll('SELECT * FROM customer_shipping_address WHERE fkcustomer_id='.$id.'');
		return $sql;
	}

	/**
	* Purpose  get  customer particular shipping details under particular customer for the particular company database
	* @param   customer id
	* @return  shipping addresses
	*/	

	public function getParticularShippingDetails($id) {
		$sql = $this->remoteDb->fetchAll('SELECT * FROM customer_shipping_address WHERE id='.$id.'');
		return $sql;
	}

	/**
	* Purpose  get all product details for the particular company database
	* @param   none
	* @return  all product details
	*/	

	public function getProductDetails() {
		$sql = $this->remoteDb->fetchAll('SELECT * FROM products ORDER BY name ASC');
		return $sql;
	}


	/**
	* Purpose  get all product details based on particular currency for the particular company database
	* @param   currency value
	* @return  product details
	*/	

	public function getCurrencyProductDetails($currency) {
		$sql = $this->remoteDb->fetchAll('SELECT * FROM products WHERE currency="'.$currency.'" ORDER BY name ASC');
		return $sql;
	}

	/**
	* Purpose  get all vendor details for the particular company database
	* @param   none
	* @return  vendor id and name
	*/	

	public function getVendorDetails() {
		$sql = $this->remoteDb->fetchAll('SELECT id,vendor_id,vendor_name,coa_link,other_coa_link FROM vendors WHERE delete_status=1 ORDER BY vendor_name ASC');
		return $sql;
	}

	/**
	* Purpose  get payment account details for the particular company database
	* @param   none
	* @return  payment account name and id
	*/	

	public function getPaymentAccount() {
		$sql = $this->remoteDb->fetchAll('SELECT id,account_name,account_type FROM account WHERE pay_status=1 AND delete_status=1 ORDER BY account_name ASC');
		return $sql;
	}

	/**
	* Purpose  get income account details for the particular company database
	* @param   none
	* @return  income account name and id
	*/	

	public function getIncomeAccount() {
		$sql = $this->remoteDb->fetchAll('SELECT id,account_name,account_type,level1,level2,debit_opening_balance,credit_opening_balance FROM account WHERE account_type=3 AND delete_status=1 AND edit_status=1 ORDER BY account_name ASC');
		return $sql;
	}

	/**
	* Purpose  get expense account details for the particular company database
	* @param   none
	* @return  expense account name and id
	*/	

	public function getExpenseAccount() {
		$sql = $this->remoteDb->fetchAll('SELECT id,account_name,account_type,level1,level2,debit_opening_balance,credit_opening_balance FROM account WHERE (account_type=4 OR (account_type=1 AND level1=2)) AND delete_status=1 AND edit_status=1 ORDER BY account_name ASC');
		return $sql;
	}

	public function getExpensePieAccount() {
		$sql = $this->remoteDb->fetchAll('SELECT id,account_name,account_type,level1,level2,debit_opening_balance,credit_opening_balance FROM account WHERE account_type=4  AND delete_status=1 ORDER BY account_name ASC');
		return $sql;
	}

	/**
	* Purpose  get all selected account details for the particular company database
	* @param   none
	* @return  all account name and id
	*/	

	public function getAllAccount() {
		$sql = $this->remoteDb->fetchAll('SELECT id,account_name,account_type FROM account WHERE delete_status=1 ORDER BY account_name ASC');
		return $sql;
	}

	/**
	* Purpose  get all the tax code details for the particular company database
	* @param   taxType if 1 its purchase and 2 its supply
	* @return  all tax code details maintained by particular company which are active
	*/	

	public function getTax() {
			$sql = $this->remoteDb->fetchAll('SELECT * FROM taxcodes WHERE tax_status=1');
			return $sql;
	}


	/**
	* Purpose : Insert income transaction for the particular company database 
	* @param   array $postVal contain form post value, company primary id $cid and transaction verified status $status
	* @return  last insert id when success
	*/
	
	public function insertIncomeTransaction($postVal,$cid,$status) {
		//print_r($postVal); die();
		$sql = $this->remoteDb->fetchOne('SELECT income_no FROM income_transaction ORDER BY id DESC');
		if(isset($sql) && !empty($sql)) {
			$income_no = ++$sql;
		} else {
			$income_no = 'INC-0000000001';
		}
		if(isset($income_no) && !empty($income_no)) {
			$getData    =   array('fkcompany_id'   				 => $cid,
							 	  'income_no'    			     => $income_no,
							 	  'date'    					 => $postVal['date'],
							 	  'receipt_no'    	   		 	 => trim($postVal['receipt']),
							 	  'fkcustomer_id'    	   		 => $postVal['customer'],
							 	  /*'fkpayment_account'    	   	 => $postVal['pay_account'],*/
							 	  'credit_term'    				 => trim($postVal['credit_term']),
							 	  'transaction_currency'    	 => trim($postVal['currency']),
							 	  'exchange_rate'      	     	 => trim($postVal['exchange_rate']),
							 	  'fkincome_type'    	   	     => trim($postVal['income_type']),
							 	  'transaction_description'    	 => stripslashes($postVal['description']),
							 	  'amount'    	   		 		 => trim($postVal['amount']),
							 	  'fkreceipt_id'    	   		 => $postVal['attached_file'],
							 	  'fktax_id'    	   			 => trim($postVal['tax_id']),
							 	  'tax_value'    	   			 => trim($postVal['tax_percentage']),
							 	  'approval_for'    	   		 => trim($postVal['approval_for']),
							 	  'transaction_status'    	   	 => $status);
			//print_r($getData); die();
			if($this->remoteDb->insert('income_transaction',$getData)) {
				$insertId  =  $this->remoteDb->lastInsertId();	
				if(isset($postVal['payment_trigger']) && $postVal['payment_trigger']==1) {
					$postVal['discount'] = 0;
					$postVal['addpay_pay_amount'] = str_replace(",","",$postVal['addpay_pay_amount']);
					if(isset($postVal['addpay_payment_discount']) && $postVal['addpay_payment_discount']==1 && isset($postVal['addpay_discount_payment_amount'])) {
						$postVal['discount'] = $postVal['addpay_discount_payment_amount'];
					}
					$payment_account = explode("_", $postVal['addpay_pay_account']);
					$postVal['addpay_account'] = $payment_account[0];
					$postVal['addpay_date'] = date('Y-m-d',strtotime($postVal['addpay_date']));
					$getData    =   array('fkiei_id'   				 => $insertId,
									 	  'date'    			     => $postVal['addpay_date'],
									 	  'fkpayment_account'    	 => trim($postVal['addpay_pay_account']),
									 	  'payment_amount'    	     => trim($postVal['addpay_pay_amount']),
									 	  'payment_method'    	   	 => trim($postVal['addpay_pay_method']),
									 	  'cheque_draft_no'    	     => trim($postVal['addpay_cheque_draft_no']),
									 	  'discount_status'    	     => trim($postVal['addpay_payment_discount']),
									 	  'discount_amount'    	   	 => trim($postVal['discount']),
									 	  'payment_description'    	 => trim($postVal['addpay_description']),
									 	  'payment_status'    	   	 => 1);
					$this->remoteDb->insert('payments',$getData);
					$lastPayId = $this->remoteDb->lastInsertId();
					if(isset($postVal['add_payment_status']) && !empty($postVal['add_payment_status'])) {
						$getDatas    =   array('payment_status'    	   	 	 => 1,
							                   'payment_id'    	   	 	 	 => $lastPayId,
											   'final_payment_date'	   	 	 => $postVal['addpay_date'],
							 	               'date_modified'				 => new Zend_Db_Expr('NOW()'));
						$this->remoteDb->update('income_transaction',$getDatas,'id='.$insertId.'');
					}
				}
				return  $insertId;	
			} else {
				return false;	
			}
		} else {
			return false;	
		}
	}


	public function insertIncomeAuditTransaction($postVal,$id,$status) {
		
		
			$getData    =   array('fkincome_id'   				 => $id,
							 	  'date'    					 => $postVal['date'],
							 	  'receipt_no'    	   		 	 => trim($postVal['receipt']),
							 	  'fkcustomer_id'    	   		 => $postVal['customer'],
							 	  /*'fkpayment_account'    	   	 => $postVal['pay_account'],*/
							 	  'credit_term'    				 => trim($postVal['credit_term']),
							 	  'transaction_currency'    	 => trim($postVal['currency']),
							 	  'exchange_rate'      	     	 => trim($postVal['exchange_rate']),
							 	  'fkincome_type'    	   	     => trim($postVal['income_type']),
							 	  'transaction_description'    	 => stripslashes($postVal['description']),
							 	  'amount'    	   		 		 => trim($postVal['amount']),
							 	  'fkreceipt_id'    	   		 => $postVal['attached_file'],
							 	  'fktax_id'    	   			 => trim($postVal['tax_id']),
							 	  'tax_value'    	   			 => trim($postVal['tax_percentage']),
							 	  'approval_for'    	   		 => trim($postVal['approval_for']),
							 	  'transaction_status'    	   	 => $status);
			//print_r($getData); die();
			if($this->remoteDb->insert('income_transaction_audit',$getData)) {
				$insertId  =  $this->remoteDb->lastInsertId();	
				return  $insertId;	
			} else {
				return false;	
			}
		
	}


	/**
	* Purpose : Insert expense transaction for the particular company database 
	* @param   array $postVal contain form post value, company primary id $cid and transaction verified status $status
	* @return  last insert id when success
	*/
	
	public function insertExpenseTransaction($postVal,$cid,$status) {
		//echo '<pre>'; print_r($postVal); echo '</pre>'; die();
		$sql = $this->remoteDb->fetchOne('SELECT expense_no FROM expense_transaction ORDER BY id DESC');
		if(isset($sql) && !empty($sql)) {
			$expense_no = ++$sql;
		} else {
			$expense_no = 'EXP-0000000001';
		}
		if(isset($expense_no) && !empty($expense_no)) {
			if($postVal['payment_discount'] ==2) {
				$postVal['discount_amount'] = 0;
			}
			$getData    =   array('fkcompany_id'   				 => $cid,
							 	  'expense_no'    			     => $expense_no,
							 	  'date'    					 => $postVal['date'],
							 	  'receipt_no'    	   		 	 => trim($postVal['receipt']),
							 	  'fkvendor_id'    	   		 	 => $postVal['vendor'], 
							 	  /*'shipping_address'    	   	 => trim($postVal['shipping_address']),*/
							 	  'credit_term'    				 => trim($postVal['credit_term']),
							 	  'transaction_currency'    	 => trim($postVal['currency']),
							 	  'exchange_rate'      	     	 => trim($postVal['exchange_rate']),
							 	  'total_gst'      	     	 	 => trim($postVal['total_gst_rate']),
							 	  /*'fkpayment_account'    	   	 => $postVal['pay_account'],*/
							 	  'due_date'    	   	    	 => trim($postVal['due_date']),
							 	  'discount_status'      	     => trim($postVal['payment_discount']),
							 	  'permit_no'    	   			 => trim($postVal['permit_no']),
							 	  'do_so_no'    	   			 => trim($postVal['do_so_no']),
							 	  'fkreceipt_id'    	   		 => $postVal['attached_file'],
							 	  'approval_for'    	   		 => trim($postVal['approval_for']),
							 	  'transaction_status'    	   	 => $status);
			//echo '<pre>'; print_r($postVal); echo '</pre>'; die();
			if($this->remoteDb->insert('expense_transaction',$getData)) {
				$lastID = $this->remoteDb->lastInsertId();
				if(isset($lastID) && !empty($lastID)) {	
					$expense_counter = $postVal['expense_counter'];
					if(isset($expense_counter) && !empty($expense_counter) && $expense_counter!=0) {
						for ($i=1; $i <= $expense_counter; $i++) { 
							$tax_id 			 = '';
							$tax_percentage 	 = '';
							$expense_type   	 =  $postVal['expense_type_'.$i];
							$product_id    		 =  trim($postVal['product_id_'.$i]);
							$product_description =  trim($postVal['product_description_'.$i]);
							$quantity   	 	 =  trim($postVal['quantity_'.$i]);
							$price    			 =  trim(str_replace(",","",$postVal['price_'.$i]));
							if(isset($expense_type) && !empty($expense_type)) {
								$expense_type = $expense_type;
							} else {
								$expense_type = NULL;
							}
							if($postVal['tax_code_'.$i]==0 || $postVal['tax_code_'.$i]=='') {
								$tax_id  = '';
								$tax_percentage = '';
							} else {
								$taxes  			 = 	explode("_",$postVal['tax_code_'.$i]);
								$tax_id 			 =  $taxes[0];
								if(isset($taxes[1]) && !empty($taxes[1])) {
									$tax_percentage  = $taxes[1];
								}
							}
						  
							$getExpenseData     =    array('fkexpense_id'    	  => $lastID,
													 	   'fkexpense_type'  	  => $expense_type,
													 	   'product_id'  		  => $product_id,
													 	   'product_description'  => $product_description,
													 	   'quantity' 		   	  => $quantity,
													 	   'unit_price'    		  => $price,
													 	   'fktax_id'    		  => $tax_id,
													 	   'tax_value'    		  => $tax_percentage);
						//	echo '<pre>'; print_r($getExpenseData); echo '</pre>'; die();
							$insertExpense  =  $this->remoteDb->insert('expense_transaction_list',$getExpenseData);
						}
					} 

					if(isset($postVal['payment_trigger']) && $postVal['payment_trigger']==1) {
					$postVal['discount'] = 0;
					$postVal['addpay_pay_amount'] = str_replace(",","",$postVal['addpay_pay_amount']);
					if(isset($postVal['addpay_payment_discount']) && $postVal['addpay_payment_discount']==1 && isset($postVal['addpay_discount_payment_amount'])) {
						$postVal['discount'] = $postVal['addpay_discount_payment_amount'];
					}
					$payment_account = explode("_", $postVal['addpay_pay_account']);
					$postVal['addpay_account'] = $payment_account[0];
					$postVal['addpay_date']  = date('Y-m-d',strtotime($postVal['addpay_date']));
					$getData    =   array('fkiei_id'   				 => $lastID,
									 	  'date'    			     => $postVal['addpay_date'],
									 	  'fkpayment_account'    	 => trim($postVal['addpay_pay_account']),
									 	  'payment_amount'    	     => trim($postVal['addpay_pay_amount']),
									 	  'payment_method'    	   	 => trim($postVal['addpay_pay_method']),
									 	  'cheque_draft_no'    	     => trim($postVal['addpay_cheque_draft_no']),
									 	  'discount_status'    	     => trim($postVal['addpay_payment_discount']),
									 	  'discount_amount'    	   	 => trim($postVal['discount']),
									 	  'payment_description'    	 => trim($postVal['addpay_description']),
									 	  'payment_status'    	   	 => 2);
					//print_r($getData); die();
					$this->remoteDb->insert('payments',$getData);
					$lastPayId = $this->remoteDb->lastInsertId();
					if(isset($postVal['add_payment_status']) && !empty($postVal['add_payment_status'])) {
						$getDatas    =   array('payment_status'    	   	 	 => 1,
											   'payment_id'    	   	 	 	 => $lastPayId,
											   'final_payment_date'	   	 	 => $postVal['addpay_date'],
							 	               'date_modified'				 => new Zend_Db_Expr('NOW()'));
						$this->remoteDb->update('expense_transaction',$getDatas,'id='.$lastID.'');
					}
				}
					return $lastID;
				}
			} else {
				return false;	
			}
		} else {
			return false;	
		}
	}




	public function insertExpenseAuditTransaction($postVal,$id,$status) {
		//echo '<pre>'; print_r($postVal); echo '</pre>'; die();
		
			if($postVal['payment_discount'] ==2) {
				$postVal['discount_amount'] = 0;
			}
			$getData    =   array('fkexpense_id'   				 => $id,
							 	  'date'    					 => $postVal['date'],
							 	  'receipt_no'    	   		 	 => trim($postVal['receipt']),
							 	  'fkvendor_id'    	   		 	 => $postVal['vendor'], 
							 	  /*'shipping_address'    	   	 => trim($postVal['shipping_address']),*/
							 	  'credit_term'    				 => trim($postVal['credit_term']),
							 	  'transaction_currency'    	 => trim($postVal['currency']),
							 	  'exchange_rate'      	     	 => trim($postVal['exchange_rate']),
							 	  'total_gst'      	     	 	 => trim($postVal['total_gst_rate']),
							 	  /*'fkpayment_account'    	   	 => $postVal['pay_account'],*/
							 	  'due_date'    	   	    	 => trim($postVal['due_date']),
							 	  'discount_status'      	     => trim($postVal['payment_discount']),
							 	  'permit_no'    	   			 => trim($postVal['permit_no']),
							 	  'do_so_no'    	   			 => trim($postVal['do_so_no']),
							 	  'fkreceipt_id'    	   		 => $postVal['attached_file'],
							 	  'approval_for'    	   		 => trim($postVal['approval_for']),
							 	  'transaction_status'    	   	 => $status);
			//echo '<pre>'; print_r($postVal); echo '</pre>'; die();
			if($this->remoteDb->insert('expense_transaction_audit',$getData)) {
				$lastID = $this->remoteDb->lastInsertId();
				if(isset($lastID) && !empty($lastID)) {	
					$expense_counter = $postVal['expense_counter'];
					if(isset($expense_counter) && !empty($expense_counter) && $expense_counter!=0) {
						for ($i=1; $i <= $expense_counter; $i++) { 
							$tax_id 			 = '';
							$tax_percentage 	 = '';
							$expense_type   	 =  $postVal['expense_type_'.$i];
							$product_id    		 =  trim($postVal['product_id_'.$i]);
							$product_description =  trim($postVal['product_description_'.$i]);
							$quantity   	 	 =  trim($postVal['quantity_'.$i]);
							$price    			 =  trim(str_replace(",","",$postVal['price_'.$i]));
							if(isset($expense_type) && !empty($expense_type)) {
								$expense_type = $expense_type;
							} else {
								$expense_type = NULL;
							}
							if($postVal['tax_code_'.$i]==0 || $postVal['tax_code_'.$i]=='') {
								$tax_id  = '';
								$tax_percentage = '';
							} else {
								$taxes  			 = 	explode("_",$postVal['tax_code_'.$i]);
								$tax_id 			 =  $taxes[0];
								if(isset($taxes[1]) && !empty($taxes[1])) {
									$tax_percentage  = $taxes[1];
								}
							}
						  
							$getExpenseData     =    array('fkexpense_id'    	  => $lastID,
													 	   'fkexpense_type'  	  => $expense_type,
													 	   'product_id'  		  => $product_id,
													 	   'product_description'  => $product_description,
													 	   'quantity' 		   	  => $quantity,
													 	   'unit_price'    		  => $price,
													 	   'fktax_id'    		  => $tax_id,
													 	   'tax_value'    		  => $tax_percentage);
						//	echo '<pre>'; print_r($getExpenseData); echo '</pre>'; die();
							$insertExpense  =  $this->remoteDb->insert('expense_transaction_list_audit',$getExpenseData);
						}
					} 

					
					return $lastID;
				}
			} else {
				return false;	
			}
		
	}

	/**
	* Purpose : Insert invoice transaction for the particular company database 
	* @param   array $postVal contain form post value, company primary id $cid  and income status $status
	* @return  last insert id when success
	*/
	
	public function insertInvoiceTransaction($postVal,$cid,$status) {
		$sql = $this->remoteDb->fetchOne('SELECT invoice_no FROM invoice ORDER BY id DESC');
		if(isset($sql) && !empty($sql)) {
			$invoice_no = ++$sql;
		} else {
			$invoice_no = 'INV-0000000001';
		}
		$invoiceSplit = explode("-", $invoice_no);
		if($postVal['invoice_custom']!=$invoiceSplit[0]) {
			$invoiceNo = $postVal['invoice_custom']."-".$invoiceSplit[1];
		} else {
			$invoiceNo = $invoice_no;
		}
		//echo $invoiceNo; die();
		if(isset($invoice_no) && !empty($invoice_no)) {
			$getData    =   array('fkcompany_id'   				 => $cid,
							 	  'invoice_no'    			     => $invoiceNo,
							 	  'date'    					 => $postVal['date'],
							 	  'fkcustomer_id'    	   		 => $postVal['customer'],
							 	  'fkshipping_address'    	   	 => trim($postVal['shipping_address']),
							 	  'credit_term'    				 => trim($postVal['credit_term']),
							 	  'transaction_currency'    	 => trim($postVal['currency']),
							 	  'exchange_rate'      	     	 => trim($postVal['exchange_rate']),
							 	  'due_date'    	   	    	 => $postVal['due_date'],
							 	  'discount_status'      	     => trim($postVal['payment_discount']),
							 	  'non_revenue_tax'    	   		 => trim($postVal['non_revenue_tax']),
							 	  'memo'    	   			     => stripslashes($postVal['memo']),
							 	  'do_so_no'    	   			 => trim($postVal['do_so_no']),
							 	  'approval_for'    	   		 => trim($postVal['approval_for']),
							 	  'invoice_status'    	   	 	 => $status);
			// echo '<pre>'; print_r($getData); echo '</pre>'; die();
			if($this->remoteDb->insert('invoice',$getData)) {
				$lastID = $this->remoteDb->lastInsertId();
				if(isset($lastID) && !empty($lastID)) {	
					$product_counter = $postVal['product_counter'];
					if(isset($product_counter) && !empty($product_counter) && $product_counter!=0) {
						for ($i=1; $i <= $product_counter; $i++) { 
							$tax_id 			 = '';
							$tax_percentage 	 = '';
							$product_id    		 =  trim($postVal['product_id_'.$i]);
							$product_description =  explode("_",$postVal['product_description_'.$i]);
							$product_desc 	     =  $product_description[0];
							$quantity   	 	 =  trim($postVal['quantity_'.$i]);
							$discount_amount     =  trim(str_replace(",","",$postVal['discount_amount_'.$i]));
							$price    			 =  trim(str_replace(",","",$postVal['price_'.$i]));
							$taxes  			 = 	explode("_",$postVal['tax_code_'.$i]);
							$tax_id 			 =  $taxes[0];
							if(isset($taxes[1]) && !empty($taxes[1])) {
								$tax_percentage = $taxes[1];
							}
							$getInvoiceData     =    array('fkinvoice_id'    	  => $lastID,
													 	   'product_id'  		  => $product_id,
													 	   'product_description'  => $product_desc,
													 	   'quantity' 		   	  => $quantity,
													 	   'discount_amount' 	  => $discount_amount,
													 	   'unit_price'    		  => $price,
													 	   'fktax_id'    		  => $tax_id,
													 	   'tax_value'    		  => $tax_percentage);
								$insertInvoice  =  $this->remoteDb->insert('invoice_product_list',$getInvoiceData);
						}
					}

					if(isset($postVal['payment_trigger']) && $postVal['payment_trigger']==1) {
					$postVal['discount'] = 0;
					$postVal['addpay_pay_amount'] = str_replace(",","",$postVal['addpay_pay_amount']);
					if(isset($postVal['addpay_payment_discount']) && $postVal['addpay_payment_discount']==1 && isset($postVal['addpay_discount_payment_amount'])) {
						$postVal['discount'] = $postVal['addpay_discount_payment_amount'];
					}
					$payment_account = explode("_", $postVal['addpay_pay_account']);
					$postVal['addpay_account'] = $payment_account[0];
					$postVal['addpay_date'] = date('Y-m-d',strtotime($postVal['addpay_date']));
					$getData    =   array('fkiei_id'   				 => $lastID,
									 	  'date'    			     => $postVal['addpay_date'],
									 	  'fkpayment_account'    	 => trim($postVal['addpay_pay_account']),
									 	  'payment_amount'    	     => trim($postVal['addpay_pay_amount']),
									 	  'payment_method'    	   	 => trim($postVal['addpay_pay_method']),
									 	  'cheque_draft_no'    	     => trim($postVal['addpay_cheque_draft_no']),
									 	  'discount_status'    	     => trim($postVal['addpay_payment_discount']),
									 	  'discount_amount'    	   	 => trim($postVal['discount']),
									 	  'payment_description'    	 => trim($postVal['addpay_description']),
									 	  'payment_status'    	   	 => 3);
					//print_r($getData); die();
					$this->remoteDb->insert('payments',$getData);
					$lastPayId = $this->remoteDb->lastInsertId();
					if(isset($postVal['add_payment_status']) && !empty($postVal['add_payment_status'])) {
						$getDatas    =   array('payment_status'    	   	 	 => 1,
											   'payment_id'    	   	 	 	 => $lastPayId,
											   'final_payment_date'	   	 	 => $postVal['addpay_date'],
							 	               'date_modified'				 => new Zend_Db_Expr('NOW()'));
						$this->remoteDb->update('invoice',$getDatas,'id='.$lastID.'');
					}
				}
					return $lastID;
				}
			} else {
				return false;	
			}
		} else {
			return false;	
		}
	}




	public function insertInvoiceAuditTransaction($postVal,$id,$status) {
		
			$getData    =   array('fkinvoice_id'   				 => $id,
							 	  'date'    					 => $postVal['date'],
							 	  'fkcustomer_id'    	   		 => $postVal['customer'],
							 	  'fkshipping_address'    	   	 => trim($postVal['shipping_address']),
							 	  'credit_term'    				 => trim($postVal['credit_term']),
							 	  'transaction_currency'    	 => trim($postVal['currency']),
							 	  'exchange_rate'      	     	 => trim($postVal['exchange_rate']),
							 	  'due_date'    	   	    	 => $postVal['due_date'],
							 	  'discount_status'      	     => trim($postVal['payment_discount']),
							 	  'non_revenue_tax'    	   		 => trim($postVal['non_revenue_tax']),
							 	  'memo'    	   			     => stripslashes($postVal['memo']),
							 	  'do_so_no'    	   			 => trim($postVal['do_so_no']),
							 	  'approval_for'    	   		 => trim($postVal['approval_for']),
							 	  'invoice_status'    	   	 	 => $status);
			// echo '<pre>'; print_r($getData); echo '</pre>'; die();
			if($this->remoteDb->insert('invoice_audit',$getData)) {
				$lastID = $this->remoteDb->lastInsertId();
				if(isset($lastID) && !empty($lastID)) {	
					$product_counter = $postVal['product_counter'];
					if(isset($product_counter) && !empty($product_counter) && $product_counter!=0) {
						for ($i=1; $i <= $product_counter; $i++) { 
							$tax_id 			 = '';
							$tax_percentage 	 = '';
							$product_id    		 =  trim($postVal['product_id_'.$i]);
							$product_description =  explode("_",$postVal['product_description_'.$i]);
							$product_desc 	     =  $product_description[0];
							$quantity   	 	 =  trim($postVal['quantity_'.$i]);
							$discount_amount     =  trim(str_replace(",","",$postVal['discount_amount_'.$i]));
							$price    			 =  trim(str_replace(",","",$postVal['price_'.$i]));
							$taxes  			 = 	explode("_",$postVal['tax_code_'.$i]);
							$tax_id 			 =  $taxes[0];
							if(isset($taxes[1]) && !empty($taxes[1])) {
								$tax_percentage = $taxes[1];
							}
							$getInvoiceData     =    array('fkinvoice_id'    	  => $lastID,
													 	   'product_id'  		  => $product_id,
													 	   'product_description'  => $product_desc,
													 	   'quantity' 		   	  => $quantity,
													 	   'discount_amount' 	  => $discount_amount,
													 	   'unit_price'    		  => $price,
													 	   'fktax_id'    		  => $tax_id,
													 	   'tax_value'    		  => $tax_percentage);
								$insertInvoice  =  $this->remoteDb->insert('invoice_product_list_audit',$getInvoiceData);
						}
					}

					return $lastID;
				}
			} else {
				return false;	
			}
		
	}


	/**
	* Purpose : Insert credit note transaction for the particular company database 
	* @param   array $postVal contain form post value, company primary id $cid  and credit status $status
	* @return  last insert id when success
	*/
	
	public function insertCreditTransaction($postVal,$cid,$status) {
		$sql = $this->remoteDb->fetchOne('SELECT credit_no FROM credit ORDER BY id DESC');
		if(isset($sql) && !empty($sql)) {
			$credit_no = ++$sql;
		} else {
			$credit_no = 'CR-0000000001';
		}
		$creditSplit = explode("-", $credit_no);
		if($postVal['credit_custom']!=$creditSplit[0]) {
			$creditNo = $postVal['credit_custom']."-".$creditSplit[1];
		} else {
			$creditNo = $credit_no;
		}
		if(isset($credit_no) && !empty($credit_no)) {
			$getData    =   array('fkcompany_id'   				 => $cid,
							 	  'credit_no'    			     => $creditNo,
							 	  'fkcustomer_id'    	   		 => $postVal['customer'],
							 	  'fkinvoice_id'    	   		 => $postVal['invoice'],
							 	  'transaction_currency'    	 => trim($postVal['currency']),
							 	  'exchange_rate'      	     	 => trim($postVal['exchange_rate']),
							 	  'date'    	   	    	 	 => $postVal['date'],
							 	  'memo'    	   			     => stripslashes($postVal['memo']),
							 	  'approval_for'    	   		 => trim($postVal['approval_for']),
							 	  'credit_status'    	   	 	 => $status);
			//echo '<pre>'; print_r($getData); echo '</pre>'; die();
			if($this->remoteDb->insert('credit',$getData)) {
				$lastID = $this->remoteDb->lastInsertId();
				if(isset($lastID) && !empty($lastID)) {	
					$product_counter = $postVal['product_counter'];
					if(isset($product_counter) && !empty($product_counter) && $product_counter!=0) {
						for ($i=1; $i <= $product_counter; $i++) { 
							$tax_id 			 = '';
							$tax_percentage 	 = '';
							$product_id    		 =  trim($postVal['product_id_'.$i]);
							$product_description =  explode("_",$postVal['product_description_'.$i]);
							$product_desc 	     =  $product_description[0];
							$quantity   	 	 =  trim($postVal['quantity_'.$i]);
							$price    			 =  trim(str_replace(",","",$postVal['price_'.$i]));
							$discount_amount     =  trim(str_replace(",","",$postVal['discount_amount_'.$i]));
							$taxes  			 = 	explode("_",$postVal['tax_code_'.$i]);
							$tax_id 			 =  $taxes[0];
							if(isset($taxes[1]) && !empty($taxes[1])) {
								$tax_percentage = $taxes[1];
							}
							if($quantity>0) {
							$getCreditData     =    array('fkcredit_id'    	  	  => $lastID,
													 	   'product_id'  		  => $product_id,
													 	   'product_description'  => $product_desc,
													 	   'quantity' 		   	  => $quantity,
													 	   'unit_price'    		  => $price,
													 	   'discount_amount' 	  => $discount_amount,
													 	   'fktax_id'    		  => $tax_id,
													 	   'tax_value'    		  => $tax_percentage);
							$insertCredit  =  $this->remoteDb->insert('credit_product_list',$getCreditData);
							}
						}
					}
					return $lastID;
				}
			} else {
				return false;	
			}
		} else {
			return false;	
		}
	}




	public function insertCreditAuditTransaction($postVal,$id,$status) {

			$getData    =   array('fkcredit_id'   				 => $id,
							 	  'fkcustomer_id'    	   		 => $postVal['customer'],
							 	  'fkinvoice_id'    	   		 => $postVal['invoice'],
							 	  'transaction_currency'    	 => trim($postVal['currency']),
							 	  'exchange_rate'      	     	 => trim($postVal['exchange_rate']),
							 	  'date'    	   	    	 	 => $postVal['date'],
							 	  'memo'    	   			     => stripslashes($postVal['memo']),
							 	  'approval_for'    	   		 => trim($postVal['approval_for']),
							 	  'credit_status'    	   	 	 => $status);
			//echo '<pre>'; print_r($getData); echo '</pre>'; die();
			if($this->remoteDb->insert('credit_audit',$getData)) {
				$lastID = $this->remoteDb->lastInsertId();
				if(isset($lastID) && !empty($lastID)) {	
					$product_counter = $postVal['product_counter'];
					if(isset($product_counter) && !empty($product_counter) && $product_counter!=0) {
						for ($i=1; $i <= $product_counter; $i++) { 
							$tax_id 			 = '';
							$tax_percentage 	 = '';
							$product_id    		 =  trim($postVal['product_id_'.$i]);
							$product_description =  explode("_",$postVal['product_description_'.$i]);
							$product_desc 	     =  $product_description[0];
							$quantity   	 	 =  trim($postVal['quantity_'.$i]);
							$price    			 =  trim(str_replace(",","",$postVal['price_'.$i]));
							$discount_amount     =  trim(str_replace(",","",$postVal['discount_amount_'.$i]));
							$taxes  			 = 	explode("_",$postVal['tax_code_'.$i]);
							$tax_id 			 =  $taxes[0];
							if(isset($taxes[1]) && !empty($taxes[1])) {
								$tax_percentage = $taxes[1];
							}
							if($quantity>0) {
							$getCreditData     =    array('fkcredit_id'    	  	  => $lastID,
													 	   'product_id'  		  => $product_id,
													 	   'product_description'  => $product_desc,
													 	   'quantity' 		   	  => $quantity,
													 	   'unit_price'    		  => $price,
													 	   'discount_amount' 	  => $discount_amount,
													 	   'fktax_id'    		  => $tax_id,
													 	   'tax_value'    		  => $tax_percentage);
							$insertCredit  =  $this->remoteDb->insert('credit_product_list_audit',$getCreditData);
							}
						}
					}
					return $lastID;
				}
			} else {
				return false;	
			}

	}



	/**
	* Purpose : Insert journal entries for the particular company database 
	* @param   array $postVal contain form post value, company primary id $cid and journal status $status
	* @return  last insert id when success
	*/
	
	public function insertJournalTransaction($postVal,$cid,$status) {
		$sql = $this->remoteDb->fetchOne('SELECT journal_no FROM journal_entries ORDER BY id DESC');
		if(isset($sql) && !empty($sql)) {
			$journal_no = ++$sql;
		} else {
			$journal_no = 'JEN-0000000001';
		}
		if(isset($journal_no) && !empty($journal_no)) {
			$getData    =   array('fkcompany_id'   				 => $cid,
							 	  'journal_no'    			     => $journal_no,
							 	  'date'    	   	    	 	 => $postVal['date'],
							 	  'description'    	   			 => stripslashes($postVal['description']),
							 	  'attachment'					 => $postVal['attached_file'],
							 	  'approval_for'    	   		 => trim($postVal['approval_for']),
							 	  'journal_status'    	   	 	 => $status);
			if($this->remoteDb->insert('journal_entries',$getData)) {
				$lastID = $this->remoteDb->lastInsertId();
				if(isset($lastID) && !empty($lastID)) {	
					$journal_counter = $postVal['journal_counter'];
					if(isset($journal_counter) && !empty($journal_counter) && $journal_counter!=0) {
						for ($i=1; $i <= $journal_counter; $i++) { 
							$account_type    	 =  trim($postVal['account_type_'.$i]);
							$journal_description =  stripslashes($postVal['journal_description_'.$i]);
							$debit   	 	 	 =  trim(str_replace(",","",$postVal['debit_'.$i]));
							$credit    			 =  trim(str_replace(",","",$postVal['credit_'.$i]));
							if(isset($account_type) && !empty($account_type)) {
								$account_type = $account_type;
							} else {
								$account_type = NULL;
							}
							$getJournalData     =     array('fkjournal_id'    	  => $lastID,
													 	   'fkaccount_id'  		  => $account_type,
													 	   'journal_description'  => $journal_description,
													 	   'debit' 		   	  	  => $debit,
													 	   'credit'    		  	  => $credit);
								$insertJournal  =  $this->remoteDb->insert('journal_entries_list',$getJournalData);
						}
					}
					return $lastID;
				}
			} else {
				return false;	
			}
		} else {
			return false;	
		}
	}



	public function insertJournalAuditTransaction($postVal,$id,$status) {
		
			$getData    =   array('fkjournal_id'   				 => $id,
							 	  'date'    	   	    	 	 => $postVal['date'],
							 	  'description'    	   			 => stripslashes($postVal['description']),
							 	  'attachment'					 => $postVal['attached_file'],
							 	  'approval_for'    	   		 => trim($postVal['approval_for']),
							 	  'journal_status'    	   	 	 => $status);
			if($this->remoteDb->insert('journal_entries_audit',$getData)) {
				$lastID = $this->remoteDb->lastInsertId();
				if(isset($lastID) && !empty($lastID)) {	
					$journal_counter = $postVal['journal_counter'];
					if(isset($journal_counter) && !empty($journal_counter) && $journal_counter!=0) {
						for ($i=1; $i <= $journal_counter; $i++) { 
							$account_type    	 =  trim($postVal['account_type_'.$i]);
							$journal_description =  stripslashes($postVal['journal_description_'.$i]);
							$debit   	 	 	 =  trim(str_replace(",","",$postVal['debit_'.$i]));
							$credit    			 =  trim(str_replace(",","",$postVal['credit_'.$i]));
							if(isset($account_type) && !empty($account_type)) {
								$account_type = $account_type;
							} else {
								$account_type = NULL;
							}
							$getJournalData     =     array('fkjournal_id'    	  => $lastID,
													 	   'fkaccount_id'  		  => $account_type,
													 	   'journal_description'  => $journal_description,
													 	   'debit' 		   	  	  => $debit,
													 	   'credit'    		  	  => $credit);
								$insertJournal  =  $this->remoteDb->insert('journal_entries_list_audit',$getJournalData);
						}
					}
					return $lastID;
				}
			} else {
				return false;	
			}

	}



	/**
	* Purpose : Update credit note transaction for the particular company database 
	* @param   array $postVal contain form post value, company primary id $cid  and credit status $status
	* @return  last insert id when success
	*/
	
	public function updateJournalTransaction($postVal,$id,$status) {
			$getData    =   array( 'date'    	   	    	 	 => $postVal['date'],
							 	  'description'    	   			 => stripslashes($postVal['description']),
							 	  'attachment'					 => $postVal['attached_file'],
							 	  'approval_for'    	   		 => trim($postVal['approval_for']),
							 	  'journal_status'    	   	 	 => $status,
							 	  'date_modified'				 => new Zend_Db_Expr('NOW()'));


			// echo '<pre>'; print_r($postVal); echo '</pre>'; die();
			if($this->remoteDb->update('journal_entries',$getData,'id='.$id.'')) {
					
					$update_journal_counter = $postVal['update_journal_counter'];
					if(isset($update_journal_counter) && !empty($update_journal_counter) && $update_journal_counter!=0) {
						for ($i=1; $i <= $update_journal_counter; $i++) { 
							$jid    		 	 =  $postVal['jid_'.$i];
							$account_type    	 =  trim($postVal['account_type_'.$i]);
							$journal_description =  stripslashes($postVal['journal_description_'.$i]);
							$debit   	 	 	 =  trim(str_replace(",","",$postVal['debit_'.$i]));
							$credit    			 =  trim(str_replace(",","",$postVal['credit_'.$i]));
							if(isset($account_type) && !empty($account_type)) {
								$account_type = $account_type;
							} else {
								$account_type = NULL;
							}
							 $getJournalData     =   array('fkaccount_id'  		  => $account_type,
													 	   'journal_description'  => $journal_description,
													 	   'debit' 		   	  	  => $debit,
													 	   'credit'    		  	  => $credit);
								$updateJournal  =  $this->remoteDb->update('journal_entries_list',$getJournalData,'id='.$jid.'');
						}
					}


					$journal_counter = $postVal['journal_counter'];
					if(isset($journal_counter) && !empty($journal_counter) && $journal_counter!=0) {
						for ($i=++$update_journal_counter; $i <= $journal_counter; $i++) { 
							$account_type    	 =  trim($postVal['account_type_'.$i]);
							$journal_description =  stripslashes($postVal['journal_description_'.$i]);
							$debit   	 	 	 =  trim(str_replace(",","",$postVal['debit_'.$i]));
							$credit    			 =  trim(str_replace(",","",$postVal['credit_'.$i]));
							if(isset($account_type) && !empty($account_type)) {
								$account_type = $account_type;
							} else {
								$account_type = NULL;
							}
							$getJournalDatas     =     array('fkjournal_id'    	  => $id,
													 	   'fkaccount_id'  		  => $account_type,
													 	   'journal_description'  => $journal_description,
													 	   'debit' 		   	  	  => $debit,
													 	   'credit'    		  	  => $credit);

								$insertJournal  =  $this->remoteDb->insert('journal_entries_list',$getJournalDatas);
						}
					}
					return true;
				
			} else {
				return true;	
			}
	}





	/**
	* Purpose : Update credit note transaction for the particular company database 
	* @param   array $postVal contain form post value, company primary id $cid  and credit status $status
	* @return  last insert id when success
	*/
	
	public function updateCreditTransaction($postVal,$id,$status) {
			$getData    =   array('fkcustomer_id'    	   		 => $postVal['customer'],
							 	  'transaction_currency'    	 => trim($postVal['currency']),
							 	  'exchange_rate'      	     	 => trim($postVal['exchange_rate']),
							 	  'date'    	   	    	 	 => $postVal['date'],
							 	  'memo'    	   			     => stripslashes($postVal['memo']),
							 	  'approval_for'    	   		 => trim($postVal['approval_for']),
							 	  'credit_status'    	   	 	 => $status,
							 	  'date_modified'				 => new Zend_Db_Expr('NOW()'));

			// echo '<pre>'; print_r($postVal); echo '</pre>'; die();
			if($this->remoteDb->update('credit',$getData,'id='.$id.'')) {
					
					$update_product_counter = $postVal['update_product_counter'];
					if(isset($update_product_counter) && !empty($update_product_counter) && $update_product_counter!=0) {
						for ($i=1; $i <= $update_product_counter; $i++) { 
							$tax_id 			 = '';
							$tax_percentage 	 = '';
							$pid    		 	 =  $postVal['pid_'.$i];
							$product_id    		 =  trim($postVal['product_id_'.$i]);
							$product_description =  explode("_",$postVal['product_description_'.$i]);
							$product_desc 	     =  $product_description[0];
							$quantity   	 	 =  trim($postVal['quantity_'.$i]);
							$price    			 =  trim(str_replace(",","",$postVal['price_'.$i]));
							$discount_amount   	 =  trim(str_replace(",","",$postVal['discount_amount_'.$i]));
							$taxes  			 = 	explode("_",$postVal['tax_code_'.$i]);
							$tax_id 			 =  $taxes[0];
							if(isset($taxes[1]) && !empty($taxes[1])) {
								$tax_percentage = $taxes[1];
							}
							if($quantity>0) {
							 $getCreditData     =    array('product_id'  		  => $product_id,
													 	   'product_description'  => $product_desc,
													 	   'quantity' 		   	  => $quantity,
													 	   'unit_price'    		  => $price,
													 	   'discount_amount'	  => $discount_amount,
													 	   'fktax_id'    		  => $tax_id,
													 	   'tax_value'    		  => $tax_percentage);
								$updateCredit  =  $this->remoteDb->update('credit_product_list',$getCreditData,'id='.$pid.'');
							}
						}
					}


					$product_counter = $postVal['product_counter'];
					if(isset($product_counter) && !empty($product_counter) && $product_counter!=0) {
						for ($i=++$update_product_counter; $i <= $product_counter; $i++) { 
							$tax_id 			 = '';
							$tax_percentage 	 = '';
							$product_id    		 =  trim($postVal['product_id_'.$i]);
							$product_description =  explode("_",$postVal['product_description_'.$i]);
							$product_desc 	     =  $product_description[0];
							$quantity   	 	 =  trim($postVal['quantity_'.$i]);
							$price    			 =  trim(str_replace(",","",$postVal['price_'.$i]));
							$discount_amount   	 =  trim(str_replace(",","",$postVal['discount_amount_'.$i]));
							$taxes  			 = 	explode("_",$postVal['tax_code_'.$i]);
							$tax_id 			 =  $taxes[0];
							if(isset($taxes[1]) && !empty($taxes[1])) {
								$tax_percentage = $taxes[1];
							}
							if($quantity>0) {
							$getCreditDatas    =      array('fkcredit_id'    	  => $id,
													 	   'product_id'  		  => $product_id,
													 	   'product_description'  => $product_desc,
													 	   'quantity' 		   	  => $quantity,
													 	   'unit_price'    		  => $price,
													 	   'discount_amount'	  => $discount_amount,
													 	   'fktax_id'    		  => $tax_id,
													 	   'tax_value'    		  => $tax_percentage);

								$insertCredit  =  $this->remoteDb->insert('credit_product_list',$getCreditDatas);
							}
						}
					}
					return true;
				
			} else {
				return true;	
			}
	}





	/**
	* Purpose : Update invoice transaction for the particular company database 
	* @param   array $postVal contain form post value, company primary id $cid  and income status $status
	* @return  last insert id when success
	*/
	
	public function updateInvoiceTransaction($postVal,$id,$status) {
			$getData    =   array('date'    					 => $postVal['date'],
							 	  'fkcustomer_id'    	   		 => $postVal['customer'],
							 	  'fkshipping_address'    	   	 => trim($postVal['shipping_address']),
							 	  'credit_term'    				 => trim($postVal['credit_term']),
							 	  'transaction_currency'    	 => trim($postVal['currency']),
							 	  'exchange_rate'      	     	 => trim($postVal['exchange_rate']),
							 	  'due_date'    	   	    	 => $postVal['due_date'],
							 	  'discount_status'      	     => trim($postVal['payment_discount']),
							 	  'non_revenue_tax'    	   		 => trim($postVal['non_revenue_tax']),
							 	  'memo'    	   			     => stripslashes($postVal['memo']),
							 	  'do_so_no'    	   			 => trim($postVal['do_so_no']),
							 	  'approval_for'    	   		 => trim($postVal['approval_for']),
							 	  'invoice_status'    	   	 	 => $status,
							 	  'date_modified'				 => new Zend_Db_Expr('NOW()'));
			// echo '<pre>'; print_r($postVal); echo '</pre>'; die();
			if($this->remoteDb->update('invoice',$getData,'id='.$id.'')) {
					
					$update_product_counter = $postVal['update_product_counter'];
					if(isset($update_product_counter) && !empty($update_product_counter) && $update_product_counter!=0) {
						for ($i=1; $i <= $update_product_counter; $i++) { 
							$tax_id 			 = '';
							$tax_percentage 	 = '';
							$pid    		 	 =  $postVal['pid_'.$i];
							$product_id    		 =  trim($postVal['product_id_'.$i]);
							$product_description =  explode("_",$postVal['product_description_'.$i]);
							$product_desc 	     =  $product_description[0];
							$quantity   	 	 =  trim($postVal['quantity_'.$i]);
							$discount_amount   	 =  trim(str_replace(",","",$postVal['discount_amount_'.$i]));
							$price    			 =  trim(str_replace(",","",$postVal['price_'.$i]));
							$taxes  			 = 	explode("_",$postVal['tax_code_'.$i]);
							$tax_id 			 =  $taxes[0];
							if(isset($taxes[1]) && !empty($taxes[1])) {
								$tax_percentage = $taxes[1];
							}
							$getInvoiceData     =    array('product_id'  		  => $product_id,
													 	   'product_description'  => $product_desc,
													 	   'quantity' 		   	  => $quantity,
													 	   'unit_price'    		  => $price,
													 	   'discount_amount'	  => $discount_amount,
													 	   'fktax_id'    		  => $tax_id,
													 	   'tax_value'    		  => $tax_percentage);
								$updateInvoice  =  $this->remoteDb->update('invoice_product_list',$getInvoiceData,'id='.$pid.'');
						}
					}


					$product_counter = $postVal['product_counter'];
					if(isset($product_counter) && !empty($product_counter) && $product_counter!=0) {
						for ($i=++$update_product_counter; $i <= $product_counter; $i++) { 
							$tax_id 			 = '';
							$tax_percentage 	 = '';
							$product_id    		 =  trim($postVal['product_id_'.$i]);
							$product_description =  explode("_",$postVal['product_description_'.$i]);
							$product_desc 	     =  $product_description[0];
							$quantity   	 	 =  trim($postVal['quantity_'.$i]);
							$discount_amount   	 =  trim(str_replace(",","",$postVal['discount_amount_'.$i]));
							$price    			 =  trim(str_replace(",","",$postVal['price_'.$i]));
							$taxes  			 = 	explode("_",$postVal['tax_code_'.$i]);
							$tax_id 			 =  $taxes[0];
							if(isset($taxes[1]) && !empty($taxes[1])) {
								$tax_percentage = $taxes[1];
							}
							$getInvoiceData     =    array('fkinvoice_id'    	  => $id,
													 	   'product_id'  		  => $product_id,
													 	   'product_description'  => $product_desc,
													 	   'quantity' 		   	  => $quantity,
													 	   'discount_amount'	  => $discount_amount,
													 	   'unit_price'    		  => $price,
													 	   'fktax_id'    		  => $tax_id,
													 	   'tax_value'    		  => $tax_percentage);
								$insertInvoice  =  $this->remoteDb->insert('invoice_product_list',$getInvoiceData);
						}
					}


					if(isset($postVal['payment_trigger']) && $postVal['payment_trigger']==1 && isset($postVal['pay_payid']) && $postVal['credit_term']==1) {
					$postVal['discount'] = 0;
					$postVal['addpay_pay_amount'] = str_replace(",","",$postVal['addpay_pay_amount']);
					if(isset($postVal['addpay_payment_discount']) && $postVal['addpay_payment_discount']==1 && isset($postVal['addpay_discount_payment_amount'])) {
						$postVal['discount'] = $postVal['addpay_discount_payment_amount'];
					}
					$payment_account = explode("_", $postVal['addpay_pay_account']);
					$postVal['addpay_account'] = $payment_account[0];
					$postVal['addpay_date'] = date('Y-m-d',strtotime($postVal['addpay_date']));
					$getData    =   array('date'    			     => $postVal['addpay_date'],
									 	  'fkpayment_account'    	 => trim($postVal['addpay_pay_account']),
									 	  'payment_amount'    	     => trim($postVal['addpay_pay_amount']),
									 	  'payment_method'    	   	 => trim($postVal['addpay_pay_method']),
									 	  'cheque_draft_no'    	     => trim($postVal['addpay_cheque_draft_no']),
									 	  'discount_status'    	     => trim($postVal['addpay_payment_discount']),
									 	  'discount_amount'    	   	 => trim($postVal['discount']),
									 	  'payment_description'    	 => trim($postVal['addpay_description']),
									 	  'payment_status'    	   	 => 3);
					//print_r($getData); die();
					$this->remoteDb->update('payments',$getData,'id='.$postVal['pay_payid'].'');
					if(isset($postVal['add_payment_status']) && !empty($postVal['add_payment_status'])) {
						$getDatas    =   array('payment_status'    	   	 	 => 1,
											   'payment_id'    	   	 	 	 => $postVal['pay_payid'],
											   'final_payment_date'	   	 	 => $postVal['addpay_date'],
							 	               'date_modified'				 => new Zend_Db_Expr('NOW()'));
						$this->remoteDb->update('invoice',$getDatas,'id='.$id.'');
					}
				} else if(isset($postVal['payment_trigger']) && $postVal['payment_trigger']==1 && !isset($postVal['pay_payid']) && $postVal['credit_term']==1) {
					$postVal['discount'] = 0;
					$postVal['addpay_pay_amount'] = str_replace(",","",$postVal['addpay_pay_amount']);
					if(isset($postVal['addpay_payment_discount']) && $postVal['addpay_payment_discount']==1 && isset($postVal['addpay_discount_payment_amount'])) {
						$postVal['discount'] = $postVal['addpay_discount_payment_amount'];
					}
					$payment_account = explode("_", $postVal['addpay_pay_account']);
					$postVal['addpay_account'] = $payment_account[0];
					$postVal['addpay_date'] = date('Y-m-d',strtotime($postVal['addpay_date']));
					$getData    =   array('fkiei_id'   				 => $id,
									 	  'date'    			     => $postVal['addpay_date'],
									 	  'fkpayment_account'    	 => trim($postVal['addpay_pay_account']),
									 	  'payment_amount'    	     => trim($postVal['addpay_pay_amount']),
									 	  'payment_method'    	   	 => trim($postVal['addpay_pay_method']),
									 	  'cheque_draft_no'    	     => trim($postVal['addpay_cheque_draft_no']),
									 	  'discount_status'    	     => trim($postVal['addpay_payment_discount']),
									 	  'discount_amount'    	   	 => trim($postVal['discount']),
									 	  'payment_description'    	 => trim($postVal['addpay_description']),
									 	  'payment_status'    	   	 => 3);
					//print_r($getData); die();
					$this->remoteDb->insert('payments',$getData);
					$lastPayId = $this->remoteDb->lastInsertId();
					if(isset($postVal['add_payment_status']) && !empty($postVal['add_payment_status'])) {
						$getDatas    =   array('payment_status'    	   	 	 => 1,
											   'payment_id'    	   	 	 	 => $lastPayId,
											   'final_payment_date'	   	 	 => $postVal['addpay_date'],
							 	               'date_modified'				 => new Zend_Db_Expr('NOW()'));
						$this->remoteDb->update('invoice',$getDatas,'id='.$id.'');
					}
					
				} else if(isset($postVal['payment_trigger']) && $postVal['payment_trigger']==0 && $postVal['credit_term']!=1 && isset($postVal['pay_payid'])) {

					$sql = $this->remoteDb->delete('payments', 'id = '.$postVal['pay_payid'].'');
					$getDatas    =   array('payment_status'    	   	 	 => 2,
											   'payment_id'    	   	 	 	 => "",
											   'final_payment_date'	   	 	 => "",
							 	               'date_modified'				 => new Zend_Db_Expr('NOW()'));
						$this->remoteDb->update('invoice',$getDatas,'id='.$id.'');
					
				}

				return true;
				
			} else {
				return true;	
			}
	}


	/**
	* Purpose :Add payment for invoice or expense or income transaction for particular company database
	* @param   array $postVal contain form post value,  and  status $status
	* @return  last insert id when success
	*/
	
	public function addPayment($postVal,$status) {
		//echo '<pre>'; print_r($postVal); echo '</pre>'; die();
			$postVal['pay_amount'] = str_replace(",","",$postVal['pay_amount']);
			$postVal['discount']   = str_replace(",","",$postVal['discount']);
			$getData    =   array('fkiei_id'   				=> $postVal['ref_id'],
							 	  'date'    			    => $postVal['date'],
							 	  'fkpayment_account'    	=> trim($postVal['pay_account']),
							 	  'payment_amount'    	     => trim($postVal['pay_amount']),
							 	  'payment_method'    	   	 => trim($postVal['pay_method']),
							 	  'cheque_draft_no'    	     => trim($postVal['cheque_draft_no']),
							 	  'discount_status'    	     => trim($postVal['payment_discount']),
							 	  'discount_amount'    	   	 => trim($postVal['discount']),
							 	  'payment_description'    	 => stripslashes($postVal['description']),
							 	  'payment_status'    	   	 => $status);
			if($this->remoteDb->insert('payments',$getData)) {
				$lastID =  $this->remoteDb->lastInsertId();
				if(isset($postVal['payment_status']) && !empty($postVal['payment_status'])) {
					$getDatas    =   array('payment_status'    	   	 	 => 1,
										   'payment_id'    	   	 	 	 => $lastID,
										   'final_payment_date'	   	 	 => $postVal['date'],
							 	           'date_modified'				 => new Zend_Db_Expr('NOW()'));
					if($status==1) {
						$this->remoteDb->update('income_transaction',$getDatas,'id='.$postVal['ref_id'].'');
					} else if($status==2) {
						$this->remoteDb->update('expense_transaction',$getDatas,'id='.$postVal['ref_id'].'');
					} else if($status==3) {
						$this->remoteDb->update('invoice',$getDatas,'id='.$postVal['ref_id'].'');
					}
				}
				return $lastID;
					
			} else {
				return false;	
			}
	}



	public function addPaymentAudit($postVal,$status) {
		//echo '<pre>'; print_r($postVal); echo '</pre>'; die();
			$postVal['pay_amount'] = str_replace(",","",$postVal['pay_amount']);
			$postVal['discount']   = str_replace(",","",$postVal['discount']);
			$getData    =   array('fkiei_id'   				=> $postVal['ref_id'],
							 	  'date'    			    => $postVal['date'],
							 	  'fkpayment_account'    	=> trim($postVal['pay_account']),
							 	  'payment_amount'    	     => trim($postVal['pay_amount']),
							 	  'payment_method'    	   	 => trim($postVal['pay_method']),
							 	  'cheque_draft_no'    	     => trim($postVal['cheque_draft_no']),
							 	  'discount_status'    	     => trim($postVal['payment_discount']),
							 	  'discount_amount'    	   	 => trim($postVal['discount']),
							 	  'payment_description'    	 => stripslashes($postVal['description']),
							 	  'payment_status'    	   	 => $status);
			if($this->remoteDb->insert('payments_audit',$getData)) {
				$lastID =  $this->remoteDb->lastInsertId();
				
				return $lastID;
					
			} else {
				return false;	
			}
	}
                                                                            


	/**
	* Purpose :Update payment for invoice or expense or income transaction for particular company database
	* @param   array $postVal contain form post value,  and  status $status
	* @return  last update id when success
	*/
	
	public function updatePayment($postVal,$status) {
			$postVal['pay_amount'] = str_replace(",","",$postVal['pay_amount']);
			$postVal['discount']   = str_replace(",","",$postVal['discount']);
			$getData    =   array('date'    			     => $postVal['date'],
							 	  'fkpayment_account'    	 => trim($postVal['pay_account']),
							 	  'payment_amount'    	     => trim($postVal['pay_amount']),
							 	  'payment_method'    	   	 => trim($postVal['pay_method']),
							 	  'cheque_draft_no'    	     => trim($postVal['cheque_draft_no']),
							 	  'discount_status'    	     => trim($postVal['payment_discount']),
							 	  'discount_amount'    	   	 => trim($postVal['discount']),
							 	  'payment_description'    	 => stripslashes($postVal['description']),
							 	  'payment_status'    	   	 => $status,
		             		      'date_modified' 		     => new Zend_Db_Expr('NOW()'));
			if($this->remoteDb->update('payments',$getData,'id='.$postVal['pay_id'].'')) {
				if(isset($postVal['payment_status']) && !empty($postVal['payment_status'])) {
					$getDatas    =   array('payment_status'    	   	 	 => 1,
										   'payment_id'    	   	 	 	 => $postVal['pay_id'],
										   'final_payment_date'	   	 	 => $postVal['date'],
							 	           'date_modified'				 => new Zend_Db_Expr('NOW()'));
					if($status==1) {
						$this->remoteDb->update('income_transaction',$getDatas,'id='.$postVal['ref_id'].'');
					} else if($status==2) {
						$this->remoteDb->update('expense_transaction',$getDatas,'id='.$postVal['ref_id'].'');
					} else if($status==3) {
						$this->remoteDb->update('invoice',$getDatas,'id='.$postVal['ref_id'].'');
					}
				} else if($postVal['pay_status']==1 && !isset($postVal['payment_status']) || empty($postVal['payment_status'])) {
					$getDatas    =   array('payment_status'    	   	 	 => 2,
										   'payment_id'    	   	 	 	 => 0,
							 	           'date_modified'				 => new Zend_Db_Expr('NOW()'));
					if($status==1) {
						$this->remoteDb->update('income_transaction',$getDatas,'id='.$postVal['ref_id'].'');
					} else if($status==2) {
						$this->remoteDb->update('expense_transaction',$getDatas,'id='.$postVal['ref_id'].'');
					} else if($status==3) {
						$this->remoteDb->update('invoice',$getDatas,'id='.$postVal['ref_id'].'');
					}
				}
				return  true;;	
			} else {
				return true;	
			}
	}


	/**
	* Purpose : mark invoice as sent for particular company database
	* @param   invoice primary,  and  status $status
	* @return  last update id when success
	*/
	
	public function markInvoiceTransaction($id,$status) {
			$getData    =   array( 'sent_status' => $status,
		             		       'date_modified' 	=> new Zend_Db_Expr('NOW()'));
			if($this->remoteDb->update('invoice',$getData,'id='.$id.'')) {
				return  true;;	
			} else {
				return false;	
			}
	}

	/**
	* Purpose : mark credit note as sent for particular company database
	* @param   credit primary,  and  status $status
	* @return  last update id when success
	*/
	
	public function markCreditTransaction($id,$status) {
			$getData    =   array( 'sent_status' => $status,
		             		       'date_modified' 	=> new Zend_Db_Expr('NOW()'));
			if($this->remoteDb->update('credit',$getData,'id='.$id.'')) {
				return  true;	
			} else {
				return false;	
			}
	}



	/**
	* Purpose : Update expense transaction for the particular company database 
	* @param   array $postVal contain form post value and company primary row id
	* @return  last update id when success
	*/
	
	public function updateExpenseTransaction($postVal,$id,$status) {
		$getData    =   	array('date'    					 => $postVal['date'],
							 	  'receipt_no'    	   		 	 => trim($postVal['receipt']),
							 	  'fkvendor_id'    	   		 	 => $postVal['vendor'],
							 	  /*'shipping_address'    	   	 => trim($postVal['shipping_address']),*/
							 	  'credit_term'    				 => trim($postVal['credit_term']),
							 	  'transaction_currency'    	 => trim($postVal['currency']),
							 	  'exchange_rate'      	     	 => trim($postVal['exchange_rate']),
							 	  'total_gst'      	     	 	 => trim($postVal['total_gst_rate']),
							 	  /*'fkpayment_account'    	   	 => $postVal['pay_account'],*/
							 	  'due_date'    	   	    	 => trim($postVal['due_date']),
							 	  'discount_status'      	     => trim($postVal['payment_discount']),
							 	  'permit_no'    	   			 => trim($postVal['permit_no']),
							 	  'do_so_no'    	   			 => trim($postVal['do_so_no']),
							 	  'fkreceipt_id'    	   		 => $postVal['attached_file'],
							 	  'approval_for'    	   		 => trim($postVal['approval_for']),
							 	  'transaction_status'    	   	 => $status,
		             		      'date_modified' 		         => new Zend_Db_Expr('NOW()'));
		//print_r($getData); die();

		if($this->remoteDb->update('expense_transaction',$getData,'id = '.$id.'')) {

			        $update_expense_counter = $postVal['update_expense_counter'];
					if(isset($update_expense_counter) && !empty($update_expense_counter) && $update_expense_counter!=0) {
						for ($i=1; $i <= $update_expense_counter; $i++) { 
							$tax_id 			 = '';
							$tax_percentage 	 = '';
							$expense_id			 =  $postVal['expense_id_'.$i];
							$expense_type   	 =  $postVal['expense_type_'.$i];
							$product_id    		 =  trim($postVal['product_id_'.$i]);
							$product_description =  trim($postVal['product_description_'.$i]);
							$quantity   	 	 =  trim($postVal['quantity_'.$i]);
							$price    			 =  trim(str_replace(",","",$postVal['price_'.$i]));
							$taxes  			 = 	explode("_",$postVal['tax_code_'.$i]);
							if(isset($expense_type) && !empty($expense_type)) {
								$expense_type = $expense_type;
							} else {
								$expense_type = NULL;
							}
							$tax_id 			 =  $taxes[0];
							if(isset($taxes[1]) && !empty($taxes[1])) {
								$tax_percentage = $taxes[1];
							}
							$getExpenseData     =    array('fkexpense_type'  	  => $expense_type,
													 	   'product_id'  		  => $product_id,
													 	   'product_description'  => $product_description,
													 	   'quantity' 		   	  => $quantity,
													 	   'unit_price'    		  => $price,
													 	   'fktax_id'    		  => $tax_id,
													 	   'tax_value'    		  => $tax_percentage);
							//echo '<pre>'; print_r($getExpenseData); echo '</pre>';
								$insertExpense  =  $this->remoteDb->update('expense_transaction_list',$getExpenseData,'id = '.$expense_id.'');
						}
					}


					$expense_counter = $postVal['expense_counter'];
					if(isset($expense_counter) && !empty($expense_counter) && $expense_counter!=0) {
						for ($i=++$update_expense_counter; $i <= $expense_counter; $i++) { 
							$tax_id 			 = '';
							$tax_percentage 	 = '';
							$expense_type   	 =  $postVal['expense_type_'.$i];
							$product_id    		 =  trim($postVal['product_id_'.$i]);
							$product_description =  trim($postVal['product_description_'.$i]);
							$quantity   	 	 =  trim($postVal['quantity_'.$i]);
							$price    			 =  trim(str_replace(",","",$postVal['price_'.$i]));
							$taxes  			 = 	explode("_",$postVal['tax_code_'.$i]);
							$tax_id 			 =  $taxes[0];
							if(isset($taxes[1]) && !empty($taxes[1])) {
								$tax_percentage = $taxes[1];
							}
							if(isset($expense_type) && !empty($expense_type)) {
								$expense_type = $expense_type;
							} else {
								$expense_type = NULL;
							}
							$getExpensesData     =    array('fkexpense_id'    	  => $id,
													 	   'fkexpense_type'  	  => $expense_type,
													 	   'product_id'  		  => $product_id,
													 	   'product_description'  => $product_description,
													 	   'quantity' 		   	  => $quantity,
													 	   'unit_price'    		  => $price,
													 	   'fktax_id'    		  => $tax_id,
													 	   'tax_value'    		  => $tax_percentage);
								$insertExpense  =  $this->remoteDb->insert('expense_transaction_list',$getExpensesData);
						}
					}


					if(isset($postVal['payment_trigger']) && $postVal['payment_trigger']==1 && isset($postVal['pay_payid']) && $postVal['credit_term']==1) {
					$postVal['discount'] = 0;
					$postVal['addpay_pay_amount'] = str_replace(",","",$postVal['addpay_pay_amount']);
					if(isset($postVal['addpay_payment_discount']) && $postVal['addpay_payment_discount']==1 && isset($postVal['addpay_discount_payment_amount'])) {
						$postVal['discount'] = $postVal['addpay_discount_payment_amount'];
					}
					$payment_account = explode("_", $postVal['addpay_pay_account']);
					$postVal['addpay_account'] = $payment_account[0];
					$postVal['addpay_date'] = date('Y-m-d',strtotime($postVal['addpay_date']));
					$getData    =   array('date'    			     => $postVal['addpay_date'],
									 	  'fkpayment_account'    	 => trim($postVal['addpay_pay_account']),
									 	  'payment_amount'    	     => trim($postVal['addpay_pay_amount']),
									 	  'payment_method'    	   	 => trim($postVal['addpay_pay_method']),
									 	  'cheque_draft_no'    	     => trim($postVal['addpay_cheque_draft_no']),
									 	  'discount_status'    	     => trim($postVal['addpay_payment_discount']),
									 	  'discount_amount'    	   	 => trim($postVal['discount']),
									 	  'payment_description'    	 => trim($postVal['addpay_description']),
									 	  'payment_status'    	   	 => 2);
					//print_r($getData); die();
					$this->remoteDb->update('payments',$getData,'id='.$postVal['pay_payid'].'');
					if(isset($postVal['add_payment_status']) && !empty($postVal['add_payment_status'])) {
						$getDatas    =   array('payment_status'    	   	 	 => 1,
											   'payment_id'    	   	 	 	 => $postVal['pay_payid'],
											   'final_payment_date'	   	 	 => $postVal['addpay_date'],
							 	               'date_modified'				 => new Zend_Db_Expr('NOW()'));
						$this->remoteDb->update('expense_transaction',$getDatas,'id='.$id.'');
					}
				} else if(isset($postVal['payment_trigger']) && $postVal['payment_trigger']==1 && !isset($postVal['pay_payid']) && $postVal['credit_term']==1) {
					$postVal['discount'] = 0;
					$postVal['addpay_pay_amount'] = str_replace(",","",$postVal['addpay_pay_amount']);
					if(isset($postVal['addpay_payment_discount']) && $postVal['addpay_payment_discount']==1 && isset($postVal['addpay_discount_payment_amount'])) {
						$postVal['discount'] = $postVal['addpay_discount_payment_amount'];
					}
					$payment_account = explode("_", $postVal['addpay_pay_account']);
					$postVal['addpay_account'] = $payment_account[0];
					$postVal['addpay_date'] = date('Y-m-d',strtotime($postVal['addpay_date']));
					$getData    =   array('fkiei_id'   				 => $id,
									 	  'date'    			     => $postVal['addpay_date'],
									 	  'fkpayment_account'    	 => trim($postVal['addpay_pay_account']),
									 	  'payment_amount'    	     => trim($postVal['addpay_pay_amount']),
									 	  'payment_method'    	   	 => trim($postVal['addpay_pay_method']),
									 	  'cheque_draft_no'    	     => trim($postVal['addpay_cheque_draft_no']),
									 	  'discount_status'    	     => trim($postVal['addpay_payment_discount']),
									 	  'discount_amount'    	   	 => trim($postVal['discount']),
									 	  'payment_description'    	 => trim($postVal['addpay_description']),
									 	  'payment_status'    	   	 => 2);
					//print_r($getData); die();
					$this->remoteDb->insert('payments',$getData);
					$lastPayId = $this->remoteDb->lastInsertId();
					if(isset($postVal['add_payment_status']) && !empty($postVal['add_payment_status'])) {
						$getDatas    =   array('payment_status'    	   	 	 => 1,
											   'payment_id'    	   	 	 	 => $lastPayId,
											   'final_payment_date'	   	 	 => $postVal['addpay_date'],
							 	               'date_modified'				 => new Zend_Db_Expr('NOW()'));
						$this->remoteDb->update('expense_transaction',$getDatas,'id='.$id.'');
					}
					
				} else if(isset($postVal['payment_trigger']) && $postVal['payment_trigger']==0 && $postVal['credit_term']!=1 && isset($postVal['pay_payid'])) {

					    $sql = $this->remoteDb->delete('payments', 'id = '.$postVal['pay_payid'].'');
					    $getDatas    =   array('payment_status'    	   	 	 => 2,
											   'payment_id'    	   	 	 	 => "",
											   'final_payment_date'	   	 	 => "",
							 	               'date_modified'				 => new Zend_Db_Expr('NOW()'));
						$this->remoteDb->update('expense_transaction',$getDatas,'id='.$id.'');
					
				}
				return true;
		} else {
			return true;
		}
	}

	/**
	* Purpose generate next invoice no for the company database
	* @param   none
	* @return  return invoice no 
	*/	

	 public function generateInvoiceNo() { 
		$sql = $this->remoteDb->fetchOne('SELECT invoice_no FROM invoice ORDER BY id DESC');
			if(isset($sql) && !empty($sql)) {
				$invoice_no = ++$sql;
			} else {
				$invoice_no = 'INV-0000000001';
			}
			return $invoice_no;
	}

	/**
	* Purpose generate next credit no for the company database
	* @param   none
	* @return  return credit no 
	*/	

	 public function generateCreditNo() { 
		$sql = $this->remoteDb->fetchOne('SELECT credit_no FROM credit ORDER BY id DESC');
			if(isset($sql) && !empty($sql)) {
				$credit_no = ++$sql;
			} else {
				$credit_no = 'CR-0000000001';
			}
			return $credit_no;
	}

	/**
	* Purpose generate next jounral primary id for file upload  for the company database
	* @param   none
	* @return  return id 
	*/	

	 public function getNextJournalTransaction() { 
		$sql = $this->remoteDb->fetchOne('SELECT id FROM journal_entries ORDER BY id DESC LIMIT 1');
			if(isset($sql) && !empty($sql)) {
				$jid = ++$sql;
			} else {
				$jid = 1;
			}
			return $jid;
	}

	/**
	* Purpose generate next expense primary id for file upload  for the company database
	* @param   none
	* @return  return id 
	*/	

	 public function getNextExpenseTransaction() { 
		$sql = $this->remoteDb->fetchOne('SELECT id FROM expense_transaction ORDER BY id DESC LIMIT 1');
			if(isset($sql) && !empty($sql)) {
				$eid = ++$sql;
			} else {
				$eid = 1;
			}
			return $eid;
	}

	/**
	* Purpose generate next income primary id for file upload  for the company database
	* @param   none
	* @return  return id 
	*/	

	 public function getNextIncomeTransaction() { 
		$sql = $this->remoteDb->fetchOne('SELECT id FROM income_transaction ORDER BY id DESC LIMIT 1');
			if(isset($sql) && !empty($sql)) {
				$iid = ++$sql;
			} else {
				$iid = 1;
			}
			return $iid;
	}

	/**
	* Purpose  delete particular income transaction from the company database
	* @param   income transaction primary id
	* @return  return true on success
	*/	

	 public function deleteIncomeTransaction($delid,$status) {  
/*	 	$where = 'fkiei_id='.$delid.' AND payment_status='.$status;
	 	$payList     = $this->remoteDb->delete('payments',$where);
		$sql = $this->remoteDb->delete('income_transaction', 'id = '.$delid.'');
			if($sql) {
				return true;
			} else {
				return false;
			}*/
			$getData    =   array('delete_status' => $status,
		             		      'date_modified' => new Zend_Db_Expr('NOW()'));
			if($this->remoteDb->update('income_transaction',$getData,'id='.$delid.'')) {
				return  true;	
			} else {
				return false;	
			}
	}

	/**
	* Purpose  delete particular expense transaction from the company database
	* @param   expense transaction primary id
	* @return  return true on success
	*/	

	 public function deleteExpenseTransaction($delid,$status) {  
/*	 	$where = 'fkiei_id='.$delid.' AND payment_status='.$status;
	 	$payList   = $this->remoteDb->delete('payments',$where);
	 	$transList = $this->remoteDb->delete('expense_transaction_list', 'fkexpense_id = '.$delid.'');
	 	if($transList) {
			$sql = $this->remoteDb->delete('expense_transaction', 'id = '.$delid.'');
			if($sql) {
					return true;
			} else {
					return false;
			}
		} else {
			return false;
		}*/
			$getData    =   array('delete_status' => $status,
		             		      'date_modified' => new Zend_Db_Expr('NOW()'));
			if($this->remoteDb->update('expense_transaction',$getData,'id='.$delid.'')) {
				return  true;	
			} else {
				return false;	
			}
	}


	/**
	* Purpose  delete particular credit note transaction from the company database
	* @param   credit note transaction primary id
	* @return  return true on success
	*/	

	 public function deleteCreditTransaction($delid,$status) {  
/*	 	$transList = $this->remoteDb->delete('credit_product_list', 'fkcredit_id = '.$delid.'');
	 	if($transList) {
			$sql = $this->remoteDb->delete('credit', 'id = '.$delid.'');
			if($sql) {
					return true;
			} else {
					return false;
			}
		} else {
			return false;
		}*/
			$getData    =   array('delete_status' => $status,
		             		      'date_modified' => new Zend_Db_Expr('NOW()'));
			if($this->remoteDb->update('credit',$getData,'id='.$delid.'')) {
				return  true;	
			} else {
				return false;	
			}
	}

	/**
	* Purpose  delete particular journal entry transaction from the company database
	* @param   journal entry transaction primary id
	* @return  return true on success
	*/	

	 public function deleteJournalTransaction($delid,$status) {  
/*	 	$transList = $this->remoteDb->delete('journal_entries_list', 'fkjournal_id = '.$delid.'');
	 	if($transList) {
			$sql = $this->remoteDb->delete('journal_entries', 'id = '.$delid.'');
			if($sql) {
					return true;
			} else {
					return false;
			}
		} else {
			return false;
		}*/
			$getData    =   array('delete_status' => $status,
		             		      'date_modified' => new Zend_Db_Expr('NOW()'));
			if($this->remoteDb->update('journal_entries',$getData,'id='.$delid.'')) {
				return  true;	
			} else {
				return false;	
			}
	}


	/**
	* Purpose  delete particular invoice and its payment details from the company database
	* @param   invoice primary id and $status as status
	* @return  return true on success
	*/	

	 public function deleteInvoiceTransaction($delid,$status) {  
	 	//$where = 'fkiei_id='.$delid.' AND payment_status='.$status;
	 	//echo $where; die();
	 	//$payList     = $this->remoteDb->delete('payments',$where);
	 	//$productList = $this->remoteDb->delete('invoice_product_list', 'fkinvoice_id = '.$delid.'');
			/*$sql = $this->remoteDb->delete('invoice', 'id = '.$delid.'');
			if($sql) {
					return true;
			} else {
					return false;
			}*/
			$getData    =   array('delete_status' => $status,
		             		      'date_modified' => new Zend_Db_Expr('NOW()'));
			if($this->remoteDb->update('invoice',$getData,'id='.$delid.'')) {
				return  true;	
			} else {
				return false;	
			}
	}


	/**
	* Purpose  delete particular payment made for invoice from the company database
	* @param   payment transaction primary id
	* @return  return true on success
	*/	

	 public function deletePayment($delid,$id,$status,$payid) {  
		$sql = $this->remoteDb->delete('payments', 'id = '.$delid.'');
			if($sql) {
				if($payid==$delid) {
					    $getDatas    =   array('payment_status'    	   	 	 => 2,
										   'payment_id'    	   	 	 	 => 0,
								 	       'date_modified'				 => new Zend_Db_Expr('NOW()'));
						if($status==1) {
							$this->remoteDb->update('income_transaction',$getDatas,'id='.$id.'');
						} else if($status==2) {
							$this->remoteDb->update('expense_transaction',$getDatas,'id='.$id.'');
						} else if($status==3) {
							$this->remoteDb->update('invoice',$getDatas,'id='.$id.'');
						}
					}
				return true;
			} else {
				return false;
			}
	}

	/**
	* Purpose : change income transaction status as verified or unverified to the particular company database 
	* @param   income transaction primary id and the status
	* @return  last update id when success
	*/
	
	public function changeIncomeTransactionStatus($id,$status) {
		/*$logSession = new Zend_Session_Namespace('sess_login');
		$uid = $logSession->id;*/
		$getData    =   array('transaction_status' => $status,
		             		  'date_modified'      => new Zend_Db_Expr('NOW()'));
		if($this->remoteDb->update('income_transaction',$getData,'id = '.$id.'')) {
			return  true;	
		} else {
			return false;	
		}
	}

	/**
	* Purpose : change credit transaction status as verified or unverified to the particular company database 
	* @param   credit transaction primary id and the status
	* @return  last update id when success
	*/
	
	public function changeCreditTransactionStatus($id,$status) {
		/*$logSession = new Zend_Session_Namespace('sess_login');
		$uid = $logSession->id;*/
		$getData    =   array('credit_status' => $status,
		             		  'date_modified'      => new Zend_Db_Expr('NOW()'));
		if($this->remoteDb->update('credit',$getData,'id = '.$id.'')) {
			return  true;	
		} else {
			return false;	
		}
	}

	/**
	* Purpose : change expense transaction status as verified or unverified to the particular company database 
	* @param   expense transaction primary id and the status
	* @return  last update id when success
	*/
	
	public function changeExpenseTransactionStatus($id,$status) {
		/*$logSession = new Zend_Session_Namespace('sess_login');
		$uid = $logSession->id;*/
		$getData    =   array('transaction_status' => $status,
		             		  'date_modified'      => new Zend_Db_Expr('NOW()'));
		if($this->remoteDb->update('expense_transaction',$getData,'id = '.$id.'')) {
			return  true;	
		} else {
			return false;	
		}
	}


	/**
	* Purpose : change invoice transaction status as verified or unverified to the particular company database 
	* @param   invoice transaction primary id and the status
	* @return  last update id when success
	*/
	
	public function changeInvoiceTransactionStatus($id,$status) {
/*		$logSession = new Zend_Session_Namespace('sess_login');
		$uid = $logSession->id;*/
		$getData    =   array('invoice_status' => $status,
		             		  'date_modified'      => new Zend_Db_Expr('NOW()'));
		if($this->remoteDb->update('invoice',$getData,'id = '.$id.'')) {
			return  true;	
		} else {
			return false;	
		}
	}

	/**
	* Purpose : change journal entry transaction status as verified or unverified to the particular company database 
	* @param   journal transaction primary id and the status
	* @return  last update id when success
	*/
	
	public function changeJournalTransactionStatus($id,$status) {
		/*$logSession = new Zend_Session_Namespace('sess_login');
		$uid = $logSession->id;*/
		$getData    =   array('journal_status' => $status,
		             		  'date_modified'      => new Zend_Db_Expr('NOW()'));
		if($this->remoteDb->update('journal_entries',$getData,'id = '.$id.'')) {
			return  true;	
		} else {
			return false;	
		}
	}

	public function approveTransaction($postVal) {
		$status = 1;
		foreach ($postVal['approve_id'] as $approve) {
			$seperateTransaction = explode('_', $approve);
			if($seperateTransaction[0]=='income') {
				$this->changeIncomeTransactionStatus($seperateTransaction[1],$status);
				$auditLog = $this->settings->insertAuditLog(6,1,'Income',$seperateTransaction[1]);
				$accountEntry = $this->accountEntry($seperateTransaction[1],1);
			}  else if($seperateTransaction[0]=='expense') {
				$this->changeExpenseTransactionStatus($seperateTransaction[1],$status);
				$auditLog = $this->settings->insertAuditLog(6,2,'Expense',$seperateTransaction[1]);
				$accountEntry = $this->accountEntry($seperateTransaction[1],2);
			}  else if($seperateTransaction[0]=='invoice') {
				$this->changeInvoiceTransactionStatus($seperateTransaction[1],$status);
				$auditLog = $this->settings->insertAuditLog(6,3,'Invoice',$seperateTransaction[1]);
				$accountEntry = $this->accountEntry($seperateTransaction[1],3);
			}  else if($seperateTransaction[0]=='credit') {
				$this->changeCreditTransactionStatus($seperateTransaction[1],$status);
				$auditLog = $this->settings->insertAuditLog(6,4,'Credit Note',$seperateTransaction[1]);
				$accountEntry = $this->accountEntry($seperateTransaction[1],4);
			}  else if($seperateTransaction[0]=='journal') {
				$this->changeJournalTransactionStatus($seperateTransaction[1],$status);
				$auditLog = $this->settings->insertAuditLog(6,5,'Journal Entry',$seperateTransaction[1]);
			} 
		}
		return true;
	}


	/**
	* Purpose : Update income transaction for the particular company database 
	* @param   array $postVal contain form post value, income primary id $id and transaction verified status $status
	* @return  last update id when success
	*/
	
	public function updateIncomeTransaction($postVal,$id,$status) {
			$getData    =   array( 'date'    					 => $postVal['date'],
							 	  'receipt_no'    	   		 	 => trim($postVal['receipt']),
							 	  'fkcustomer_id'    	   		 => $postVal['customer'],
							 	  /*'fkpayment_account'    	   	 => $postVal['pay_account'],*/
							 	  'credit_term'    				 => trim($postVal['credit_term']),
							 	  'transaction_currency'    	 => trim($postVal['currency']),
							 	  'exchange_rate'      	     	 => trim($postVal['exchange_rate']),
							 	  'fkincome_type'    	   	     => trim($postVal['income_type']),
							 	  'transaction_description'    	 => stripslashes($postVal['description']),
							 	  'amount'    	   		 		 => trim($postVal['amount']),
							 	  'fkreceipt_id'    	   		 => trim($postVal['attached_file']),
							 	  'fktax_id'    	   			 => trim($postVal['tax_id']),
							 	  'tax_value'    	   			 => trim($postVal['tax_percentage']),
							 	  'approval_for'    	   		 => trim($postVal['approval_for']),
							 	  'transaction_status'    	   	 => $status);
			//print_r($getData); die();
			if($this->remoteDb->update('income_transaction',$getData,'id = '.$id.'')) {

				if(isset($postVal['payment_trigger']) && $postVal['payment_trigger']==1 && isset($postVal['pay_payid']) && $postVal['credit_term']==1) {
					$postVal['discount'] = 0;
					$postVal['addpay_pay_amount'] = str_replace(",","",$postVal['addpay_pay_amount']);
					if(isset($postVal['addpay_payment_discount']) && $postVal['addpay_payment_discount']==1 && isset($postVal['addpay_discount_payment_amount'])) {
						$postVal['discount'] = $postVal['addpay_discount_payment_amount'];
					}
					$payment_account = explode("_", $postVal['addpay_pay_account']);
					$postVal['addpay_account'] = $payment_account[0];
					$postVal['addpay_date'] = date('Y-m-d',strtotime($postVal['addpay_date']));
					$getData    =   array('date'    			     => $postVal['addpay_date'],
									 	  'fkpayment_account'    	 => trim($postVal['addpay_pay_account']),
									 	  'payment_amount'    	     => trim($postVal['addpay_pay_amount']),
									 	  'payment_method'    	   	 => trim($postVal['addpay_pay_method']),
									 	  'cheque_draft_no'    	     => trim($postVal['addpay_cheque_draft_no']),
									 	  'discount_status'    	     => trim($postVal['addpay_payment_discount']),
									 	  'discount_amount'    	   	 => trim($postVal['discount']),
									 	  'payment_description'    	 => trim($postVal['addpay_description']),
									 	  'payment_status'    	   	 => 1);
					//print_r($getData); die();
					$this->remoteDb->update('payments',$getData,'id='.$postVal['pay_payid'].'');
					if(isset($postVal['add_payment_status']) && !empty($postVal['add_payment_status'])) {
						$getDatas    =   array('payment_status'    	   	 	 => 1,
											   'payment_id'    	   	 	 	 => $postVal['pay_payid'],
											   'final_payment_date'	   	 	 => $postVal['addpay_date'],
							 	               'date_modified'				 => new Zend_Db_Expr('NOW()'));
						$this->remoteDb->update('income_transaction',$getDatas,'id='.$id.'');
					}
				} else if(isset($postVal['payment_trigger']) && $postVal['payment_trigger']==1 && !isset($postVal['pay_payid']) && $postVal['credit_term']==1) {
					$postVal['discount'] = 0;
					$postVal['addpay_pay_amount'] = str_replace(",","",$postVal['addpay_pay_amount']);
					if(isset($postVal['addpay_payment_discount']) && $postVal['addpay_payment_discount']==1 && isset($postVal['addpay_discount_payment_amount'])) {
						$postVal['discount'] = $postVal['addpay_discount_payment_amount'];
					}
					$payment_account = explode("_", $postVal['addpay_pay_account']);
					$postVal['addpay_account'] = $payment_account[0];
					$postVal['addpay_date'] = date('Y-m-d',strtotime($postVal['addpay_date']));
					$getData    =   array('fkiei_id'   				 => $id,
									 	  'date'    			     => $postVal['addpay_date'],
									 	  'fkpayment_account'    	 => trim($postVal['addpay_pay_account']),
									 	  'payment_amount'    	     => trim($postVal['addpay_pay_amount']),
									 	  'payment_method'    	   	 => trim($postVal['addpay_pay_method']),
									 	  'cheque_draft_no'    	     => trim($postVal['addpay_cheque_draft_no']),
									 	  'discount_status'    	     => trim($postVal['addpay_payment_discount']),
									 	  'discount_amount'    	   	 => trim($postVal['discount']),
									 	  'payment_description'    	 => trim($postVal['addpay_description']),
									 	  'payment_status'    	   	 => 1);
					//print_r($getData); die();
					$this->remoteDb->insert('payments',$getData);
					$lastPayId = $this->remoteDb->lastInsertId();
					if(isset($postVal['add_payment_status']) && !empty($postVal['add_payment_status'])) {
						$getDatas    =   array('payment_status'    	   	 	 => 1,
											   'payment_id'    	   	 	 	 => $lastPayId,
											   'final_payment_date'	   	 	 => $postVal['addpay_date'],
							 	               'date_modified'				 => new Zend_Db_Expr('NOW()'));
						$this->remoteDb->update('income_transaction',$getDatas,'id='.$id.'');
					}
					
				} else if(isset($postVal['payment_trigger']) && $postVal['payment_trigger']==0 && $postVal['credit_term']!=1 && isset($postVal['pay_payid'])) {

					    $sql = $this->remoteDb->delete('payments', 'id = '.$postVal['pay_payid'].'');
					    $getDatas    =   array('payment_status'    	   	 	 => 2,
											   'payment_id'    	   	 	 	 => "",
											   'final_payment_date'	   	 	 => "",
							 	               'date_modified'				 => new Zend_Db_Expr('NOW()'));
						$this->remoteDb->update('income_transaction',$getDatas,'id='.$id.'');
					
				}
				return  true;	
			} else {
				return true;	
			}
	}

	/**
	* Purpose : Entry of accounts once the transaction is verified
	* @param    id value,  and  status $status
	* @return   true when success
	*/
	
	public function accountEntry($id,$status) {
		if($status==1) {
			$sql = $this->remoteDb->fetchAll('SELECT date,amount,tax_value FROM income_transaction WHERE id='.$id.' AND transaction_status=1');
			$payment = $this->remoteDb->fetchOne('SELECT SUM(payment_amount) as pay_amount FROM payments WHERE fkiei_id='.$id.' AND payment_status=1');
			$previousData = $this->remoteDb->fetchAll('SELECT id,account_entry_id FROM accounting_entries WHERE fkiei_id='.$id.' AND entry_type=1');
			if(isset($previousData) && !empty($previousData)) {
				foreach ($previousData as $prev) {
					if($prev['account_entry_id']==1) {
						$firstId = $prev['id'];
					} else if($prev['account_entry_id']==2) {
						$pyId = $prev['id'];
					} else if($prev['account_entry_id']==3) {
						$payId = $prev['id'];
					} else if($prev['account_entry_id']==4) {
						$thirdId = $prev['id'];
					} else if($prev['account_entry_id']==5) {
						$secondId = $prev['id'];
					} 
				}
			}
			//print_r($previousData); die();
			if(isset($sql) && !empty($sql)) {
				foreach ($sql as $result) {

					$taxTotal   =  ($result['amount'] * $result['tax_value']) / 100;
					$grandTotal = $result['amount'] + $taxTotal;

					if(isset($payment) && !empty($payment)) {

					if(isset($payId) && !empty($payId)) {	
						$payData    =   array('entry_date'    => $result['date'],
										 	  'account_entry_id'  => 3,
										 	  'amount'    	  => $payment,
										 	  'entry_type'    => $status,
										 	  'entry_status'  => 1,
										 	  'expiry_status' => 1,
		             		  				  'date_modified' => new Zend_Db_Expr('NOW()'));

						$this->remoteDb->update('accounting_entries',$payData,'id = '.$payId.'');					  	

					  } else {
						$payData    =   array('fkiei_id'    => $id,
										 	  'entry_date'    => $result['date'],
										 	  'account_entry_id'  => 3,
										 	  'amount'    	  => $payment,
										 	  'entry_type'    => $status,
										 	  'entry_status'  => 1);

						$this->remoteDb->insert('accounting_entries',$payData);
					}

					} 

				  if(isset($firstId) && !empty($firstId)) {
					$firstData    =   array('entry_date'    => $result['date'],
										 	  'account_entry_id'  => 1,
										 	  'amount'    	  => $grandTotal,
										 	  'entry_type'    => $status,
										 	  'entry_status'  => 1,
										 	  'expiry_status' => 1,
		             		  				  'date_modified' => new Zend_Db_Expr('NOW()'));		
					$this->remoteDb->update('accounting_entries',$firstData,'id = '.$firstId.'');	
				   } else {	
					$firstData    =   array('fkiei_id'    => $id,
										 	  'entry_date'    => $result['date'],
										 	  'account_entry_id'  => 1,
										 	  'amount'    	  => $grandTotal,
										 	  'entry_type'    => $status,
										 	  'entry_status'  => 1);		
					$this->remoteDb->insert('accounting_entries',$firstData);				
				   }

				  if(isset($secondId) && !empty($secondId)) {
					$secondData    =  array('entry_date'    => $result['date'],
									 	  'account_entry_id'  => 5,
									 	  'amount'    	  => $result['amount'],
									 	  'entry_type'    => $status,
									 	  'entry_status'  => 2,
									 	  'expiry_status' => 1,
		             		 			  'date_modified' => new Zend_Db_Expr('NOW()'));
					$this->remoteDb->update('accounting_entries',$secondData,'id = '.$secondId.'');	
				   } else {
					$secondData    =  array('fkiei_id'    => $id,
									 	  'entry_date'    => $result['date'],
									 	  'account_entry_id'  => 5,
									 	  'amount'    	  => $result['amount'],
									 	  'entry_type'    => $status,
									 	  'entry_status'  => 2);
					$this->remoteDb->insert('accounting_entries',$secondData);
				  }

				  if(isset($thirdId) && !empty($thirdId)) {
					$thirdData    =  array('entry_date'    => $result['date'],
									 	  'account_entry_id'  => 4,
									 	  'amount'    	  => $taxTotal,
									 	  'entry_type'    => $status,
									 	  'entry_status'  => 2,
									 	  'expiry_status' => 1,
		             		 			  'date_modified' => new Zend_Db_Expr('NOW()'));
					$this->remoteDb->update('accounting_entries',$thirdData,'id = '.$thirdId.'');	
				   } else {
					$thirdData    =  array('fkiei_id'    => $id,
									 	  'entry_date'    => $result['date'],
									 	  'account_entry_id'  => 4,
									 	  'amount'    	  => $taxTotal,
									 	  'entry_type'    => $status,
									 	  'entry_status'  => 2);
					$this->remoteDb->insert('accounting_entries',$thirdData);
				  }
					//print_r($getData); die();
				}
				return true;
			} else {
				return true;
			}
		} else if($status==2) {
			$date = $this->remoteDb->fetchAll('SELECT date,total_gst FROM expense_transaction WHERE id='.$id.' AND transaction_status=1');
			$expenseTransaction = $this->remoteDb->fetchAll('SELECT sum(unit_price * quantity) as amount,sum((unit_price * quantity) * tax_value / 100) as tax_amount FROM expense_transaction_list WHERE fkexpense_id='.$id.'');
			$payment = $this->remoteDb->fetchOne('SELECT SUM(payment_amount) as pay_amount FROM payments WHERE fkiei_id='.$id.' AND payment_status=2');
			$previousData = $this->remoteDb->fetchAll('SELECT id,account_entry_id FROM accounting_entries WHERE fkiei_id='.$id.' AND entry_type=2');
			if(isset($previousData) && !empty($previousData)) {
				foreach ($previousData as $prev) {
					if($prev['account_entry_id']==1) {
						$firsfftId = $prev['id']; 
					} else if($prev['account_entry_id']==2) {
						$firstId = $prev['id'];
					} else if($prev['account_entry_id']==3) {
						$payId = $prev['id'];
					} else if($prev['account_entry_id']==4) {
						$thirdId = $prev['id'];
					} else if($prev['account_entry_id']==5) {
						$secondId = $prev['id'];
					} 
				}
			}
			if(isset($date) && !empty($date)) {
				foreach ($date as  $dat) {
					$dates  		= $dat['date'];
					$total_gst_rate = $dat['total_gst'];
				}
			}
			if(isset($expenseTransaction) && !empty($expenseTransaction)) {
				foreach ($expenseTransaction as  $exp) {
					$amount = $exp['amount'];
					if($total_gst_rate!=0.00) {
						$taxTotal = 0.00;
					} else {
						$taxTotal = $exp['tax_amount'];
					}
					$grandTotal = $amount+$taxTotal;
				}
			}
			//print_r($date); die();
			if(isset($dates) && !empty($dates)) {

					if(isset($payment) && !empty($payment)) {

					if(isset($payId) && !empty($payId)) {	
						$payData    =   array('entry_date'    => $dates,
										 	  'account_entry_id'  => 3,
										 	  'amount'    	  => $payment,
										 	  'entry_type'    => $status,
										 	  'entry_status'  => 2,
										 	  'expiry_status' => 1,
		             		  				  'date_modified' => new Zend_Db_Expr('NOW()'));

						$this->remoteDb->update('accounting_entries',$payData,'id = '.$payId.'');					  	

					  } else {
						$payData    =   array('fkiei_id'    => $id,
										 	  'entry_date'    => $dates,
										 	  'account_entry_id'  => 3,
										 	  'amount'    	  => $payment,
										 	  'entry_type'    => $status,
										 	  'entry_status'  => 2);

						$this->remoteDb->insert('accounting_entries',$payData);
					}

					} 

				  if(isset($firstId) && !empty($firstId)) {
					$firstData    =   array('entry_date'    => $dates,
										 	  'account_entry_id'  => 2,
										 	  'amount'    	  => $grandTotal,
										 	  'entry_type'    => $status,
										 	  'entry_status'  => 2,
										 	  'expiry_status' => 1,
		             		  				  'date_modified' => new Zend_Db_Expr('NOW()'));		
					$this->remoteDb->update('accounting_entries',$firstData,'id = '.$firstId.'');
				   } else {	
					$firstData    =   array('fkiei_id'    => $id,
										 	  'entry_date'    => $dates,
										 	  'account_entry_id'  => 2,
										 	  'amount'    	  => $grandTotal,
										 	  'entry_type'    => $status,
										 	  'entry_status'  => 2);		
					$this->remoteDb->insert('accounting_entries',$firstData);	
				   }

				return true;
			} else {
				return true;
			}					
		} else if($status==3) {
			$date = $this->remoteDb->fetchOne('SELECT date FROM invoice WHERE id='.$id.' AND invoice_status=1');
			$invoiceTransaction = $this->remoteDb->fetchAll('SELECT sum(unit_price * quantity - discount_amount) as amount,sum((unit_price * quantity - discount_amount) * tax_value / 100) as tax_amount FROM invoice_product_list WHERE fkinvoice_id='.$id.'');
			$payment = $this->remoteDb->fetchOne('SELECT SUM(payment_amount) as pay_amount FROM payments WHERE fkiei_id='.$id.' AND payment_status=3');
			$previousData = $this->remoteDb->fetchAll('SELECT id,account_entry_id FROM accounting_entries WHERE fkiei_id='.$id.' AND entry_type=3');
			if(isset($previousData) && !empty($previousData)) {
				foreach ($previousData as $prev) {
					if($prev['account_entry_id']==1) {
						$firstId = $prev['id']; 
					} else if($prev['account_entry_id']==2) {
						$pyId = $prev['id'];
					} else if($prev['account_entry_id']==3) {
						$payId = $prev['id'];
					} else if($prev['account_entry_id']==4) {
						$thirdId = $prev['id'];
					} else if($prev['account_entry_id']==5) {
						$secondId = $prev['id'];
					} 
				}
			}
			if(isset($invoiceTransaction) && !empty($invoiceTransaction)) {
				foreach ($invoiceTransaction as  $inv) {
					$amount = $inv['amount'];
					$taxTotal = $inv['tax_amount'];
					$grandTotal = $amount+$taxTotal;
				}
			}
			//print_r($date); die();
			if(isset($date) && !empty($date)) {


					if(isset($payment) && !empty($payment)) {

					if(isset($payId) && !empty($payId)) {	
						$payData    =   array('entry_date'    => $date,
										 	  'account_entry_id'  => 3,
										 	  'amount'    	  => $payment,
										 	  'entry_type'    => $status,
										 	  'entry_status'  => 1,
										 	  'expiry_status' => 1,
		             		  				  'date_modified' => new Zend_Db_Expr('NOW()'));

						$this->remoteDb->update('accounting_entries',$payData,'id = '.$payId.'');					  	

					  } else {
						$payData    =   array('fkiei_id'    => $id,
										 	  'entry_date'    => $date,
										 	  'account_entry_id'  => 3,
										 	  'amount'    	  => $payment,
										 	  'entry_type'    => $status,
										 	  'entry_status'  => 1);

						$this->remoteDb->insert('accounting_entries',$payData);
					}

					} 

				  if(isset($firstId) && !empty($firstId)) {
					$firstData    =   array('entry_date'    => $date,
										 	  'account_entry_id'  => 1,
										 	  'amount'    	  => $grandTotal,
										 	  'entry_type'    => $status,
										 	  'entry_status'  => 1,
										 	  'expiry_status' => 1,
		             		  				  'date_modified' => new Zend_Db_Expr('NOW()'));		
					$this->remoteDb->update('accounting_entries',$firstData,'id = '.$firstId.'');	
				   } else {	
					$firstData    =   array('fkiei_id'    => $id,
										 	  'entry_date'    => $date,
										 	  'account_entry_id'  => 1,
										 	  'amount'    	  => $grandTotal,
										 	  'entry_type'    => $status,
										 	  'entry_status'  => 1);		
					$this->remoteDb->insert('accounting_entries',$firstData);				
				   }

				  if(isset($secondId) && !empty($secondId)) {
					$secondData    =  array('entry_date'    => $date,
									 	  'account_entry_id'  => 5,
									 	  'amount'    	  => $amount,
									 	  'entry_type'    => $status,
									 	  'entry_status'  => 2,
									 	  'expiry_status' => 1,
		             		 			  'date_modified' => new Zend_Db_Expr('NOW()'));
					$this->remoteDb->update('accounting_entries',$secondData,'id = '.$secondId.'');	
				   } else {
					$secondData    =  array('fkiei_id'    => $id,
									 	  'entry_date'    => $date,
									 	  'account_entry_id'  => 5,
									 	  'amount'    	  => $amount,
									 	  'entry_type'    => $status,
									 	  'entry_status'  => 2);
					$this->remoteDb->insert('accounting_entries',$secondData);
				  }

				  if(isset($thirdId) && !empty($thirdId)) {
					$thirdData    =  array('entry_date'    => $date,
									 	  'account_entry_id'  => 4,
									 	  'amount'    	  => $taxTotal,
									 	  'entry_type'    => $status,
									 	  'entry_status'  => 2,
									 	  'expiry_status' => 1,
		             		 			  'date_modified' => new Zend_Db_Expr('NOW()'));
					$this->remoteDb->update('accounting_entries',$thirdData,'id = '.$thirdId.'');	
				   } else {
					$thirdData    =  array('fkiei_id'    => $id,
									 	  'entry_date'    => $date,
									 	  'account_entry_id'  => 4,
									 	  'amount'    	  => $taxTotal,
									 	  'entry_type'    => $status,
									 	  'entry_status'  => 2);
					$this->remoteDb->insert('accounting_entries',$thirdData);
				  }
					//print_r($getData); die();
				return true;
			} else {
				return true;
			}
		}  else if($status==4) {
			$date = $this->remoteDb->fetchOne('SELECT date FROM credit WHERE id='.$id.' AND credit_status=1');
			$creditTransaction = $this->remoteDb->fetchAll('SELECT sum(unit_price * quantity - discount_amount) as amount,sum((unit_price * quantity - discount_amount) * tax_value / 100) as tax_amount FROM credit_product_list WHERE fkcredit_id='.$id.'');
			$previousData = $this->remoteDb->fetchAll('SELECT id,account_entry_id FROM accounting_entries WHERE fkiei_id='.$id.' AND entry_type=4');
			if(isset($previousData) && !empty($previousData)) {
				foreach ($previousData as $prev) {
					if($prev['account_entry_id']==1) {
						$firstId = $prev['id']; 
					} else if($prev['account_entry_id']==2) {
						$pyId = $prev['id'];
					} else if($prev['account_entry_id']==3) {
						$pyId = $prev['id'];
					} else if($prev['account_entry_id']==4) {
						$thirdId = $prev['id'];
					} else if($prev['account_entry_id']==5) {
						$secondId = $prev['id'];
					} 
				}
			}
			if(isset($creditTransaction) && !empty($creditTransaction)) {
				foreach ($creditTransaction as  $credit) {
					$amount = $credit['amount'];
					$taxTotal = $credit['tax_amount'];
					$grandTotal = $amount+$taxTotal;
				}
			}
			//print_r($date); die();
			if(isset($date) && !empty($date)) {

				  if(isset($firstId) && !empty($firstId)) {
					$firstData    =   array('entry_date'    => $date,
										 	  'account_entry_id'  => 1,
										 	  'amount'    	  => $grandTotal,
										 	  'entry_type'    => $status,
										 	  'entry_status'  => 2,
										 	  'expiry_status' => 1,
		             		  				  'date_modified' => new Zend_Db_Expr('NOW()'));		
					$this->remoteDb->update('accounting_entries',$firstData,'id = '.$firstId.'');	
				   } else {	
					$firstData    =   array('fkiei_id'    => $id,
										 	  'entry_date'    => $date,
										 	  'account_entry_id'  => 1,
										 	  'amount'    	  => $grandTotal,
										 	  'entry_type'    => $status,
										 	  'entry_status'  => 2);		
					$this->remoteDb->insert('accounting_entries',$firstData);				
				   }

				  if(isset($secondId) && !empty($secondId)) {
					$secondData    =  array('entry_date'    => $date,
									 	  'account_entry_id'  => 5,
									 	  'amount'    	  => $amount,
									 	  'entry_type'    => $status,
									 	  'entry_status'  => 1,
									 	  'expiry_status' => 1,
		             		 			  'date_modified' => new Zend_Db_Expr('NOW()'));
					$this->remoteDb->update('accounting_entries',$secondData,'id = '.$secondId.'');	
				   } else {
					$secondData    =  array('fkiei_id'    => $id,
									 	  'entry_date'    => $date,
									 	  'account_entry_id'  => 5,
									 	  'amount'    	  => $amount,
									 	  'entry_type'    => $status,
									 	  'entry_status'  => 1);
					$this->remoteDb->insert('accounting_entries',$secondData);
				  }

				  if(isset($thirdId) && !empty($thirdId)) {
					$thirdData    =  array('entry_date'    => $date,
									 	  'account_entry_id'  => 4,
									 	  'amount'    	  => $taxTotal,
									 	  'entry_type'    => $status,
									 	  'entry_status'  => 1,
									 	  'expiry_status' => 1,
		             		 			  'date_modified' => new Zend_Db_Expr('NOW()'));
					$this->remoteDb->update('accounting_entries',$thirdData,'id = '.$thirdId.'');	
				   } else {
					$thirdData    =  array('fkiei_id'    => $id,
									 	  'entry_date'    => $date,
									 	  'account_entry_id'  => 4,
									 	  'amount'    	  => $taxTotal,
									 	  'entry_type'    => $status,
									 	  'entry_status'  => 1);
					$this->remoteDb->insert('accounting_entries',$thirdData);
				  }
					
				return true;
			} else {
				return true;
			}
		} 
	}

	/**
	* Purpose : Entry of accounts of payments once the transaction is verified
	* @param    id value,  and  status $status
	* @return   true when success
	*/
	
	public function accountEntryPayment($id,$status) {
		if($status==1) {
			$payment = $this->remoteDb->fetchOne('SELECT SUM(payment_amount) as pay_amount FROM payments WHERE fkiei_id='.$id.'');
			$previousData = $this->remoteDb->fetchAll('SELECT id,account_entry_id FROM accounting_entries WHERE fkiei_id='.$id.' AND entry_type=1 AND account_entry_id=3');
			if(isset($previousData) && !empty($previousData)) {
				foreach ($previousData as $prev) {
						$payId = $prev['id'];
				}
			}

				if(isset($payment) && !empty($payment)) {

					if(isset($payId) && !empty($payId)) {	
						$payData    =   array('entry_date'    => $result['date'],
										 	  'account_entry_id'  => 3,
										 	  'amount'    	  => $payment,
										 	  'entry_type'    => $status,
										 	  'entry_status'  => 1,
										 	  'expiry_status' => 1,
		             		  				  'date_modified' => new Zend_Db_Expr('NOW()'));

						$this->remoteDb->update('accounting_entries',$payData,'id = '.$payId.'');					  	

					  } else {
						$payData    =   array('fkiei_id'    => $id,
										 	  'entry_date'    => $result['date'],
										 	  'account_entry_id'  => 3,
										 	  'amount'    	  => $payment,
										 	  'entry_type'    => $status,
										 	  'entry_status'  => 1);

						$this->remoteDb->insert('accounting_entries',$payData);
					}

					} 

		} 
	}

	/**
	* Purpose : Change expire status of accounting entry
	* @param   primary id and the status
	* @return  true when success
	*/
	
	public function accountEntryExpired($id,$status) {
		$getData    =   array('expiry_status' => 2,
		             		  'date_modified' => new Zend_Db_Expr('NOW()'));
		if($this->remoteDb->update('accounting_entries',$getData,'fkiei_id = '.$id.' AND entry_type='.$status.'')) {
			return  true;	
		} else {
			return false;	
		}
	}


	/**
	* Purpose : Count of all pending income transactions for particular super user or manager account
	* @param   login session id or proxy id
	* @return  return total counts
	*/
	
	public function pendingIncomeTransactions($id) {
		$sql = $this->remoteDb->fetchAll('SELECT id FROM income_transaction WHERE transaction_status=2 AND approval_for='.$id.'');
		$rowCount = count($sql);
		return $rowCount;
	}

	/**
	* Purpose : Count of all pending expense transactions for particular super user or manager account
	* @param   login session id or proxy id
	* @return  return total counts
	*/
	
	public function pendingExpenseTransactions($id) {
		$sql = $this->remoteDb->fetchAll('SELECT id FROM expense_transaction WHERE transaction_status=2 AND approval_for='.$id.'');
		$rowCount = count($sql);
		return $rowCount;
	}

	/**
	* Purpose : Count of all pending invoice transactions for particular super user or manager account
	* @param   login session id or proxy id
	* @return  return total counts
	*/
	
	public function pendingInvoiceTransactions($id) {
		$sql = $this->remoteDb->fetchAll('SELECT id FROM invoice WHERE invoice_status=2 AND approval_for='.$id.'');
		$rowCount = count($sql);
		return $rowCount;
	}

	/**
	* Purpose : Count of all pending credit note transactions for particular super user or manager account
	* @param   login session id or proxy id
	* @return  return total counts
	*/
	
	public function pendingCreditTransactions($id) {
		$sql = $this->remoteDb->fetchAll('SELECT id FROM credit WHERE credit_status=2 AND approval_for='.$id.'');
		$rowCount = count($sql);
		return $rowCount;
	}

	/**
	* Purpose : Count of all pending journal transactions for particular super user or manager account
	* @param   login session id or proxy id
	* @return  return total counts
	*/
	
	public function pendingJournalTransactions($id) {
		$sql = $this->remoteDb->fetchAll('SELECT id FROM journal_entries WHERE journal_status=2 AND approval_for='.$id.'');
		$rowCount = count($sql);
		return $rowCount;
	}

	/**
	* Purpose  get cash and cash equivalent details for the particular company database
	* @param   none
	* @return  account name,type,levels and id
	*/	

	public function getCashAccount() {
		$sql = $this->remoteDb->fetchAll('SELECT id,account_name,account_type,level1,level2 FROM account WHERE account_type=1 AND level1=1 AND level2=1 AND delete_status=1 ORDER BY account_name ASC');
		return $sql;
	}

	/**
	* Purpose  get payment income account details for the particular company database
	* @param   none
	* @return  payment income account name,type,levels and id
	*/	

	public function getPaymentIncomeAccount($id='') {
		if(isset($id) && !empty($id)) {
			$sql = $this->remoteDb->fetchAll('SELECT id,account_name,account_type,level1,level2 FROM account WHERE account_type=1 AND level1=1 AND id = '.$id.' AND delete_status=1 ORDER BY level2,account_name ASC');
			return $sql;
		} else {
			$sql = $this->remoteDb->fetchAll('SELECT id,account_name,account_type,level1,level2 FROM account WHERE account_type=1 AND level1=1 AND (level2=4 OR level2=5) AND delete_status=1 ORDER BY level2,account_name ASC');
			return $sql;
		}
	}

	/**
	* Purpose : check receipt no for income already exists for the particular company database
	* @param   receipt input no
	* @return  true if exists
	*/

	public function checkIncomeReceipt($receiptNo,$id='') {
		if(isset($id) && !empty($id)) {
			$sql = $this->remoteDb->fetchOne('SELECT id FROM income_transaction WHERE receipt_no="'.trim($receiptNo).'" AND id!='.$id.'');
			return $sql;
		} else {
			$sql = $this->remoteDb->fetchOne('SELECT id FROM income_transaction WHERE receipt_no="'.trim($receiptNo).'"');
			return $sql;
		}
	}

	/**
	* Purpose  get payment expense account details for the particular company database
	* @param   none
	* @return  payment expense account name,type,levels and id
	*/	

	public function getPaymentExpenseAccount($id='') {
		if(isset($id) && !empty($id)) {
			$sql = $this->remoteDb->fetchAll('SELECT id,account_name,account_type,level1,level2 FROM account WHERE account_type=2 AND level1=1 AND id = '.$id.' AND delete_status=1 ORDER BY level2,account_name ASC');
			return $sql;
		} else {
			$sql = $this->remoteDb->fetchAll('SELECT id,account_name,account_type,level1,level2 FROM account WHERE account_type=2 AND level1=1 AND (level2=3 OR level2=8) AND delete_status=1 ORDER BY level2,account_name ASC');
			return $sql;
		}
	}

	/**
	* Purpose : check receipt no for expense already exists for the particular company database
	* @param   receipt input no
	* @return  true if exists
	*/

	public function checkExpenseReceipt($receiptNo,$id='') {
		if(isset($id) && !empty($id)) {
			$sql = $this->remoteDb->fetchOne('SELECT id FROM expense_transaction WHERE receipt_no="'.trim($receiptNo).'" AND id!='.$id.'');
			return $sql;
		} else {
			$sql = $this->remoteDb->fetchOne('SELECT id FROM expense_transaction WHERE receipt_no="'.trim($receiptNo).'"');
			return $sql;
		}
	}

	/**
	* Purpose  get all the tax code details for the particular company database
	* @param   taxType if 1 its purchase and 2 its supply
	* @return  all tax code details maintained by particular company which are active
	*/	

	public function getSalesTax($taxType) {
			$sql = $this->remoteDb->fetchAll('SELECT * FROM taxcodes WHERE tax_status=1 AND tax_type='.$taxType.'');
			return $sql;
	}

	/**
	* Purpose  get all credit note issued under particular invoice for the particular company database
	* @param   invoice id
	* @return  product details
	*/	

	public function getCreditInvoiceProductList() {
		$sql = $this->remoteDb->fetchAll('SELECT t1.id,t1.fkinvoice_id,t2.product_description,t2.product_id,t2.quantity,t2.unit_price FROM `credit` as t1 INNER JOIN `credit_product_list` as t2 ON(t1.id=t2.fkcredit_id) WHERE t1.delete_status=1');
		return $sql;
	}

	/**
	* Purpose  get approve user email for the particular company database
	* @param   user primary id
	* @return  get approve user email  for particular company 
	*/	

	public function getApproveUserEmail($id) {
			$sql = $this->_db->fetchOne('SELECT username FROM login_credentials WHERE id='.$id.'');
			return $sql;
	}

	/**
	* Purpose  get IrasTax available
	* @param   tax type
	* @return  get all tax code based on type
	*/	

	public function getIrasTax($type='') {
		if(isset($type) && !empty($type)) {
			$sql = $this->_db->fetchAll('SELECT * FROM taxcodes WHERE type='.$type.' AND status=1');
			return $sql;
		} else {
			$sql = $this->_db->fetchAll('SELECT * FROM taxcodes');
			return $sql;
		}
	}

	public function getTransactionNo($id,$type) {
		if($type==1) {
			$sql = $this->remoteDb->fetchOne('SELECT income_no FROM income_transaction WHERE id='.$id.'');
			return $sql;
		} else if($type==2) {
			$sql = $this->remoteDb->fetchOne('SELECT expense_no FROM expense_transaction WHERE id='.$id.'');
			return $sql;
		} else if($type==3) {
			$sql = $this->remoteDb->fetchOne('SELECT invoice_no FROM invoice WHERE id='.$id.'');
			return $sql;
		} else if($type==4) {
			$sql = $this->remoteDb->fetchOne('SELECT credit_no FROM credit WHERE id='.$id.'');
			return $sql;
		} else if($type==5) {
			$sql = $this->remoteDb->fetchOne('SELECT journal_no FROM journal_entries WHERE id='.$id.'');
			return $sql;
		}
	}

}