<?php

class ContactsController extends Zend_Controller_Action
{
    protected $_flastMessenger = null;

    protected $_contactFields = array(
        'first_name',
        'last_name',
        'phone',
        'address',
        'address2',
        'city',
        'state',
        'postal',
        'country'
        );

    /**
     * init
	 *
	 * Initialize the controller
     * 
     * @access public
     * @return void
     */
    public function init()
    {
        /* Initialize action controller here */
		$this->_flashMessenger = $this->_helper->getHelper('FlashMessenger');
    }

    /**
     * indexAction
	 *
	 * Display a list of contacts
     * 
     * @access public
     * @return void
     */
    public function indexAction()
    {
        // action body
		$contacts = new Model_Contacts();
		$this->view->contacts = $contacts->fetchAll();
		$this->view->messages = $this->_flashMessenger->getMessages();
    }

    /**
     * addAction
	 *
	 * Display a form for adding a new contact and
	 * process that form.
     * 
     * @access public
     * @return void
     */
    public function addAction()
    {
        // action body
		$form = $this->createForm();

		$form->setAction('/contacts/add');
		
		$this->view->form = $form;

		if(isset($_POST['submit']))
		{
			if($form->isValid($_POST))
			{
				$contact = $this->createContact($_POST);

				$contact->save();
				$this->_helper->getHelper('FlashMessenger')->addMessage('New Contact Saved.');
				$this->_redirect('/contacts');
			}
		}
    }

    /**
     * editAction 
	 *
	 * Display a form for editing an existing contact and
	 * process that form.
     * 
     * @access public
     * @return void
     */
    public function editAction()
    {
        // action body
		$contact = $this->createContact(isset($_POST['submit']) ? $_POST : array('id' => $this->_getParam('id')));

		$form = $this->createForm($contact);

		$this->view->form = $form;

		if(isset($_POST['submit']))
		{
			if($form->isValid($_POST))
			{
				$contact = $this->createContact($_POST);

				$contact->save();
				$this->_helper->getHelper('FlashMessenger')->addMessage('Contact Updated.');
				$this->_redirect('/contacts');
			}
		}
    }

    /**
     * viewAction 
	 *
	 * View the data for an existing contact.
     * 
     * @access public
     * @return void
     */
    public function viewAction()
    {
        // action body
		$contact = $this->createContact(isset($_POST['submit']) ? $_POST : array('id' => $this->_getParam('id')));

		$this->view->contactData = $this->createViewData($this->createForm($contact));
    }

    /**
     * deleteAction 
	 *
	 * Display a form to confirm deleting an existing contact and
	 * process that form, deleting the contact.
     * 
     * @access public
     * @return void
     */
    public function deleteAction()
    {
        // action body
		$this->viewAction();

		$form = $this->createConfirmationForm();

		$this->view->form = $form;

		if(isset($_POST['submit']))
		{
			if($form->isValid($_POST))
			{
				$contact = $this->createContact(array('id' => $this->_getParam('id')));

				$contact->delete();
				$this->_helper->getHelper('FlashMessenger')->addMessage('Contact Deleted.');
				$this->_redirect('/contacts');
			}
		}
    }


	/**
	 * createViewData 
	 *
	 * Organize the form data for viewing a contact but not editing.
	 * 
	 * @param Zend_Form $form 
	 * @access protected
	 * @return array Labels and Values for showing form data
	 */
	protected function createViewData($form)
	{
		$viewData = array();

		foreach($form->getElements() as $element)
		{
			if(!in_array($element->getName(), $this->_contactFields))
			{
				continue;
			}

			$viewData[$element->getName()] = array(
				'label' => $element->getLabel(),
				'value' => $element->getValue(),
			);
		}

		return $viewData;
	}

    /**
     * createContact 
	 *
	 * Create a contact from the values provided.
     * 
     * @param array $values 
     * @access protected
     * @return Model_Contact
     */
    protected function createContact($values)
    {
		$contacts = new Model_Contacts();
		$contact = null;

		if (isset($values['id']))
		{
			$contactSet = $contacts->find($values['id']);
			if(isset($contactSet[0]))
			{
				$contact = $contactSet[0];
			}
		}

		if($contact === null)
		{
			$contact = $contacts->fetchNew();
		}

		foreach($this->_contactFields as $field)
		{
			if(isset($values[$field]))
			{
				$contact->$field = $values[$field];
			}
		}

		return $contact;
    }

    /**
     * createForm
	 *
	 * Create a form.  If a contact is provided,
	 * add the hidden ID field and pre-fill the
	 * values from the contact.
     * 
     * @param Model_Contact $contact 
     * @access protected
     * @return Zend_Form
     */
    protected function createForm($contact = null)
    {
		$form = new Zend_Form();

		$elements = array();

		if ($contact !== null) 
		{
			$elements = $elements + array(
				'id' => array(
					'type'    => 'hidden',
					'options' => array(
					),
				),
			);
		}

		$elements = $elements + array(
			'first_name' => array(
				'type'    => 'text',
				'options' => array(
					'label'      => 'First Name',
					'required'   => true,
					'validators' => array(
						array('AlNum',
							true,
							array(
								'allowWhiteSpace' => true,
								'messages' => array(
									Zend_Validate_Alnum::STRING_EMPTY => 'Value cannot be empty.',
									Zend_Validate_Alnum::NOT_ALNUM    => 'Value must be alpha-numeric.',
								),
							),
						),
					),
				),
			),
			'last_name' => array(
				'type'    => 'text',
				'options' => array(
					'label'      => 'Last Name',
					'required'   => true,
					'validators' => array(
						array('AlNum',
							true,
							array(
								'allowWhiteSpace' => true,
								'messages' => array(
									Zend_Validate_Alnum::STRING_EMPTY => 'Value cannot be empty.',
									Zend_Validate_Alnum::NOT_ALNUM    => 'Value must be alpha-numeric.',
								),
							),
						),
					),
				),
			),
			'phone' => array(
				'type'    => 'text',
				'options' => array(
					'label'      => 'Phone Number',
					'required'   => true,
					'validators' => array(
						array('Digits',
							true,
							array(
								'messages' => array(
									Zend_Validate_Digits::STRING_EMPTY => 'Value cannot be empty.',
									Zend_Validate_Digits::NOT_DIGITS   => 'Value must be numeric.',
								),
							),
						),
					),
				),
			),
			'address' => array(
				'type'    => 'text',
				'options' => array(
					'label'      => 'Address',
					'required'   => true,
					'validators' => array(
						array('AlNum',
							true,
							array(
								'allowWhiteSpace' => true,
								'messages' => array(
									Zend_Validate_Alnum::STRING_EMPTY => 'Value cannot be empty.',
									Zend_Validate_Alnum::NOT_ALNUM    => 'Value must be alpha-numeric.',
								),
							),
						),
					),
				),
			),
			'address2' => array(
				'type'    => 'text',
				'options' => array(
					'label'      => 'Address 2',
					'validators' => array(
						array('AlNum',
							true,
							array(
								'allowWhiteSpace' => true,
								'messages' => array(
									Zend_Validate_Alnum::STRING_EMPTY => 'Value cannot be empty.',
									Zend_Validate_Alnum::NOT_ALNUM    => 'Value must be alpha-numeric.',
								),
							),
						),
					),
				),
			),
			'city' => array(
				'type'    => 'text',
				'options' => array(
					'label'      => 'City',
					'required'   => true,
					'validators' => array(
						array('AlNum',
							true,
							array(
								'allowWhiteSpace' => true,
								'messages' => array(
									Zend_Validate_Alnum::STRING_EMPTY => 'Value cannot be empty.',
									Zend_Validate_Alnum::NOT_ALNUM    => 'Value must be alpha-numeric.',
								),
							),
						),
					),
				),
			),
			'state' => array(
				'type'    => 'text',
				'options' => array(
					'label'      => 'State/Province',
					'validators' => array(
						array('AlNum',
							true,
							array(
								'allowWhiteSpace' => true,
								'messages' => array(
									Zend_Validate_Alnum::STRING_EMPTY => 'Value cannot be empty.',
									Zend_Validate_Alnum::NOT_ALNUM    => 'Value must be alpha-numeric.',
								),
							),
						),
					),
				),
			),
			'postal' => array(
				'type'    => 'text',
				'options' => array(
					'label'      => 'Postal Code',
					'validators' => array(
						array('AlNum',
							true,
							array(
								'messages' => array(
									Zend_Validate_Alnum::STRING_EMPTY => 'Value cannot be empty.',
									Zend_Validate_Alnum::NOT_ALNUM    => 'Value must be alpha-numeric.',
								),
							),
						),
					),
				),
			),
			'country' => array(
				'type'    => 'text',
				'options' => array(
					'label'      => 'Country',
					'required'   => true,
					'validators' => array(
						array('AlNum',
							true,
							array(
								'allowWhiteSpace' => true,
								'messages' => array(
									Zend_Validate_Alnum::STRING_EMPTY => 'Value cannot be empty.',
									Zend_Validate_Alnum::NOT_ALNUM    => 'Value must be alpha-numeric.',
								),
							),
						),
					),
				),
			),
			'submit' => array(
				'type'    => 'submit',
				'options' => array(),
			),
		);

		foreach($elements as $name => $element)
		{
			$form->addElement($element['type'], $name, $element['options']);
		}

		if($contact !== null)
		{
			$form->populate($contact->toArray());
		}

		return $form;
    }


	/**
	 * createConfirmationForm 
	 *
	 * Create a form for confirming the user's choice.
	 * 
	 * @access protected
	 * @return Zend_Form
	 */
	protected function createConfirmationForm()
	{
		$form = new Zend_Form();

		$elements = array(
			'submit' => array(
				'type'    => 'submit',
				'options' => array(
					'label' => 'Yes',
				),
			),
		);

		foreach($elements as $name => $element)
		{
			$form->addElement($element['type'], $name, $element['options']);
		}

		return $form;
	}
}





