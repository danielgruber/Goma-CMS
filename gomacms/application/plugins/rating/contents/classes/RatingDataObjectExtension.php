<?php defined("IN_GOMA") OR die();

/**
 * Extends pages with checkbox and content for rating.
 *
 * @author	Goma-Team
 * @license	GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @package	Goma\Framework
 * @version 1.1
 */
class RatingDataObjectExtension extends DataObjectExtension
{
    /**
     * add db-field for switching
     */
    static $db = array(
        'rating' => 'int(1)'
    );
    /**
     * set defaults
     */
    static $default = array(
        "rating" => 0
    );

    /**
     * appends rating
     * @param HTMLNode $content
     */
    public function appendContent(&$content)
    {
        if ($this->getOwner()->rating)
            $content->prepend(Rating::draw("page_" . $this->getOwner()->id));
    }

    /**
     * renders the field in the form
     * @param Form $form
     */
    public function getForm(&$form)
    {
        $form->meta->add(new Checkbox('rating', lang('exp_gomacms_rating.allow_rate')), 7);
    }
}

gObject::extend("pages", "RatingDataObjectExtension");
