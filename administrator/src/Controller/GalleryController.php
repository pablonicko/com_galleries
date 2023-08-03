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

        parent::save();

        $jinput = Factory::getApplication()->input;
        $datag = $jinput->post->get('jform', null, 'array');
        
        
        $file_images = $jinput->files->get('jform');
        $db = Factory::getDBO();

        if (isset($datag['id']) && $datag['id'] === "") {
            $query = $db->getQuery(true)
                ->select('max(id)')
                ->from('#__galleries');
            $db->setQuery($query);
            $datag['id'] = $db->loadResult();
        }

        $foldergallery = "/media/com_galleries/galleries/" . $datag['id'];
        // Create the uploads folder if not exists in /images folder
        if ( !Folder::exists(JPATH_SITE . $foldergallery) ) {
               Folder::create(JPATH_SITE . $foldergallery);
        }

        if (!(count($file_images['gallery_images']) == 1 && $file_images['gallery_images'][0]["name"] == ''))
        {
            foreach ($file_images['gallery_images'] as &$file) 
            {
                $filename = File::makeSafe($file['name']);
                $filename = str_ireplace('.jpg', '.webp', $filename);
                $filename = str_ireplace('.png', '.webp', $filename);
                $filename = str_ireplace('.jpeg', '.webp', $filename);
                $image_url = $foldergallery . DIRECTORY_SEPARATOR . $filename;
                File::upload( $file['tmp_name'], JPATH_SITE . $image_url );
                $this->create_mask(JPATH_SITE . $image_url);
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

    public function create_mask($originalimageurl){
        // Load the watermark and the picture to apply it
        $type = exif_imagetype($originalimageurl);
        switch ($type) { 
            case 1 : 
                $im = imagecreatefromgif($originalimageurl); 
            break; 
            case 2 : 
                $im = imagecreatefromjpeg($originalimageurl); 
            break; 
            case 3 : 
                $im = imagecreatefrompng($originalimageurl);
            break; 
        } 

        // Primero crearemos nuestra imagen de la estampa manualmente desde GD
        //$estampa = imagecreatetruecolor(100, 70);
        //imagefilledrectangle($estampa, 0, 0, 99, 69, 0x0000FF);
        //imagefilledrectangle($estampa, 9, 9, 90, 60, 0xFFFFFF);
        //imagestring($estampa, 5, 20, 20, 'MiPrimerBook', 0x0000FF);
        //imagestring($estampa, 3, 20, 40, '(c) 2015', 0x0000FF);
            
        $watermark = imagecreatefrompng(JPATH_SITE .'/media/com_galleries/watermark.png');
        // Set the margins for the watermark and get the hight and width of the image into the watermark
        $margin_right = 0;
        $margin_bottom = 0;
        $sx = imagesx($watermark);
        $sy = imagesy($watermark);
        $dest_x = floor(imagesx($im)/2) - floor($sx/2) - $margin_right;
        $dest_y = floor(imagesy($im)/2) - floor($sy/2) - $margin_bottom;
        if ($dest_y < 0){
            $dest_y = imagesy($im) - $sy;
        }   
        // Merge the stamp with our photo with an opacity of 50%
        //imagecopymerge($im, $watermark, imagesx($im) - $sx - $margin_right, imagesy($im) - $sy - $margin_bottom, 0, 0, imagesx($watermark), imagesy($watermark), 50);
        imagecopy($im, $watermark, $dest_x, $dest_y, 0, 0, imagesx($watermark), imagesy($watermark));
        // Guardar la imagen en un archivo y liberar memoria
        #imagepng($im, $originalimageurl);
        imagewebp($im, $originalimageurl);
        imagedestroy($im);
        imagedestroy($watermark);
    }
}
