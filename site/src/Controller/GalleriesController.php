<?php
/**
 * @version    1.0.0
 * @package    com_galleries
 * @author     Pablo Tortul <pablonicko@gmail.com>
 * @copyright  2022 Pablo Tortul
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Pablotortul\Component\Galleries\Site\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Error;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Utilities\ArrayHelper;

/**
 * Galleries class.
 *
 * @since  1.0.0
 */
class GalleriesController extends FormController
{
	/**
	 * Proxy for getModel.
	 *
	 * @param   string  $name    The model name. Optional.
	 * @param   string  $prefix  The class prefix. Optional
	 * @param   array   $config  Configuration array for model. Optional
	 *
	 * @return  object	The model
	 *
	 * @since   1.0.0
	 */
	public function getModel($name = 'Galleries', $prefix = 'Site', $config = array())
	{
		return parent::getModel($name, $prefix, array('ignore_request' => true));
	}

	/**
     * Save galleries images before save the form
     *
     * @since   1.0.0
     */
    public function saveorder($key = NULL, $urlVar = NULL)
    {
		$jinput = Factory::getApplication()->input;
		$qdata = $jinput->post->get('quantity', null, 'array');
		$igdata = $jinput->post->get('imgurl', null, 'array');
		$gid = $jinput->get->get('gid');
		$oname = $jinput->post->get('oname');
		
		$user = Factory::getUser();
		
		$db = Factory::getDBO();
		// Create a new query object.
        $query = $db->getQuery(true);
        // table columns. 

        $image_columns = array('name', 'user_id', 'gallery_id', 'status', 'state', 'created_by', 'createdate');
        // table values.
        $image_values = array($db->quote($oname), $user->id, $gid, 1, 1, $user->id, $db->quote(date("Y-m-d H:i:s")));

        // Prepare the insert query.
        $query
            ->insert($db->quoteName('#__orders'))
            ->columns($db->quoteName($image_columns))
            ->values(implode(',', $image_values));
        $db->setQuery($query);
        $db->execute();
        $order_id = $db->insertid();

        foreach ($qdata as $image_id => $quantity) {
        	// Create a new query object.
	        $query = $db->getQuery(true);
	        // table columns.
	        $oi_columns = array('order_id', 'image_id', 'quantity', 'createDate');
	        // table values.
	        $oi_values = array($order_id, $image_id, $quantity, $db->quote(date("Y-m-d H:i:s")));

	        // Prepare the insert query.
	        $query
	            ->insert($db->quoteName('#__orders_images'))
	            ->columns($db->quoteName($oi_columns))
	            ->values(implode(',', $oi_values));

	        // Set the query using our newly populated query object and execute it.
	        $db->setQuery($query);
	        $db->execute();  
        }

        $mailer = Factory::getMailer();
		$config = Factory::getConfig();

		$sender = array( 
			$config->get( 'mailfrom' ),
			$config->get( 'fromname' ) );
		//var_dump(Text::_('COM_GALLERIES_ORDER_EMAIL_SUBJECT')); die();
		//$adminuser = Factory::getUser(113);
		$recipient = $config->get( 'mailfrom' );
		$mailer->addRecipient($recipient);
		$mailer->setSender($sender);
        $mailer->addReplyTo($config->get( 'mailfrom' ));
		$mailer->setSubject(Text::_('COM_GALLERIES_ORDER_EMAIL_SUBJECT'));
		$body   = '<p>'.str_replace('%USERNAME%', $user->name, Text::_('COM_GALLERIES_ORDER_EMAIL_HEADER')).'</p>
					<table border="1" cellspadding="0" cellspacing="0" style="width: 100%">
					<thead >
						<tr >
						<th width="80%">'.Text::_('COM_GALLERIES_ORDER_EMAIL_TITLE_IMAGE').'</th>
						<th width="20%" align="middle">'.Text::_('COM_GALLERIES_ORDER_EMAIL_TITLE_QUANTITY').'</th>
						</tr>
					</thead>
					<tbody>';
		
		
		$datares = array();
		$totalq = 0;
		$k=1;
		$mainfolder = '/media/com_galleries/galleries/' . $gid .'/';
		foreach ($igdata as $image_id => $url) {
			$totalq += $qdata[$image_id];			
			$body   .= '<tr class="row'.( $k % 2).'">
							<td>
								<img height="100px" src="'.URI::base().$url.'" /> <span>'.str_replace($mainfolder,'',$url).'</span>
							</td>
							<td align="middle">
							<span>'.$qdata[$image_id].'</span>
							</td>
						</tr>';	
			$k = 1 - $k;
						
		}
				
		$body   .= '<tr >
				<td>Total</td>
				<td>'.$totalq.'</td>
				</tr>';			
		$body   .= '</tbody></table>';
		$mailer->isHTML(true);
		$mailer->Encoding = 'base64';
		$mailer->setBody($body);
		$send = $mailer->Send();
		
		if ( $send !== true ) {
			Error::raiseWarning( 100, str_replace('%%ADMINEMAIL%%', $config->get( 'mailfrom' ), Text::_('COM_GALLERIES_ORDER_EMAIL_ERROR_SEND')) );
		} else {
			Factory::getApplication()->enqueueMessage(Text::_('COM_GALLERIES_ORDER_EMAIL_SUCCESS'));
		}

        // Redirect the user if not logged in
		$this->setRedirect(Route::_('index.php?option=com_galleries'));
    }
}
