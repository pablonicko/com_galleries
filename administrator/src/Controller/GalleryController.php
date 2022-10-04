<?php
/**
 * @version    1.0.0
 * @package    com_galleries
 * @author     Pablo Tortul <pablonicko@gmail.com>
 * @copyright  2022 Pablo Tortul
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Pablotortul\Component\Galleries\Administrator\Controller;

defined('_JEXEC') or die;

use \Joomla\CMS\MVC\Controller\FormController;
use \Joomla\CMS\Filesystem\File;
use \Joomla\CMS\Filesystem\Folder;
use \Joomla\CMS\Factory;
use \Joomla\Data\DataObject;
use \Joomla\CMS\Response\JsonResponse;

/**
 * Gallery controller class.
 *
 * @since  1.0.0
 */
class GalleryController extends FormController
{
	protected $view_list = 'galleries';

    /**
     * Save galleries images before save the form
     *
     * @since   1.0.0
     */
    public function save($key = NULL, $urlVar = NULL)
    {
        //jimport('joomla.filesystem.folder');
        //jimport('joomla.filesystem.file');

        $jinput = Factory::getApplication()->input;
        $datag = $jinput->post->get('jform', null, 'array');
        $foldergallery = "/media/com_galleries/galleries/" . $datag['id'];
        // Create the uploads folder if not exists in /images folder
        if ( !Folder::exists(JPATH_SITE . $foldergallery) ) {
               Folder::create(JPATH_SITE . $foldergallery);
        }
        
        $file_images = $jinput->files->get('jform');
        $db = Factory::getDBO();

        if (!(count($file_images['gallery_images']) == 1 && $file_images['gallery_images'][0]["name"] == ''))
        {
            foreach ($file_images['gallery_images'] as &$file) 
            {
                $filename = File::makeSafe($file['name']);
                $image_url = $foldergallery . DIRECTORY_SEPARATOR . $filename;
                File::upload( $file['tmp_name'], JPATH_SITE . $image_url );
                // Create a new query object.
                $query = $db->getQuery(true);
                // table columns.
                $image_columns = array('name', 'image_url', 'createDate');
                // table values.
                $image_values = array($db->quote($file['name']), $db->quote($image_url), $db->quote(date("Y-m-d H:i:s")));

                // Prepare the insert query.
                $query
                    ->insert($db->quoteName('#__images'))
                    ->columns($db->quoteName($image_columns))
                    ->values(implode(',', $image_values));

                // Set the query using our newly populated query object and execute it.
                $db->setQuery($query);
                $db->execute();
                $image_id = $db->insertid();

                // Create a new query object.
                $query = $db->getQuery(true);
                // table columns.
                $ig_columns = array('gallery_id', 'image_id');
                // table values.
                $ig_values = array($datag['id'], $image_id);

                // Prepare the insert query.
                $query
                    ->insert($db->quoteName('#__gallery_images'))
                    ->columns($db->quoteName($ig_columns))
                    ->values(implode(',', $ig_values));

                // Set the query using our newly populated query object and execute it.
                $db->setQuery($query);
                $db->execute();

            }
        }

        parent::save();
    }

    public function remove()
    {
        $image_gallery_id    = Factory::getApplication()->input->get('gid');

        $db = Factory::getDbo();
        $query = $db->getQuery(true);

        $query
            ->select('`#__gallery_images`.*')
            ->from($db->quoteName('#__gallery_images', '#__gallery_images'))
            ->where($db->quoteName('#__gallery_images.image_id') . ' = '. $image_gallery_id);

        $db->setQuery($query);
        $image_gallery = $db->loadObject();
        //object(stdClass)#645 (3) { ["id"]=> int(27) ["gallery_id"]=> int(1) ["image_id"]=> int(29) }

        if ($image_gallery) {
            $query = $db->getQuery(true);
            $query->delete($db->quoteName('#__gallery_images'));
            $query->where($db->quoteName('id') . ' = '.$image_gallery->id);
            $db->setQuery($query);

            $result_delete = $db->execute();

            if ($result_delete) {
                $query = $db->getQuery(true);
                $query
                    ->select('`#__images`.*')
                    ->from($db->quoteName('#__images', '#__images'))
                    ->where($db->quoteName('#__images.id') . ' = '. $image_gallery->image_id);

                $db->setQuery($query);
                $image = $db->loadObject();

                $query = $db->getQuery(true);
                $query->delete($db->quoteName('#__images'));
                $query->where($db->quoteName('id') . ' = '.$image_gallery->image_id);
                $db->setQuery($query);

                $result_delete = $db->execute();

                if ($result_delete) {
                    File::delete(JPATH_SITE . $image->image_url);
                }
            }
        }

        $data = [
            'id' => $image_gallery_id
        ];

        $response = new JsonResponse($data);

        echo $response;

    }
}
