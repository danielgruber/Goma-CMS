<?php defined("IN_GOMA") OR die();

/**
 * Controller for Rating.
 *
 * @author	Goma-Team
 * @license	GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @package	Goma\Framework
 * @version 1.1
 */
class RatingController extends Controller
{
    /**
     * @param Request $request
     * @param bool $subController
     * @return string
     */
    public function handleRequest($request, $subController = false)
    {
        $this->Init($request);

        $name = strtolower($request->getParam("name"));
        $rate = $request->getParam("rate");

        $ratingRecord = Rating::getRatingByName($name);
        if(!$ratingRecord->hasAlreadyVoted($this->request->getRemoteAddr())) {
            $ratingRecord->rates++;
            $ratingRecord->rating += $rate;
            $ratingRecord->rators = serialize(array_merge(array($this->request->getRemoteAddr()), (array)unserialize($ratingRecord->rators)));
            $ratingRecord->writeToDB(false, true);

            if ($this->getRequest()->isJSResponse()) {
                $response = new AjaxResponse;
                $response->exec('$("#rating_' . $name . '").html("' . convert::raw2js($ratingRecord->stars) . '<div class=\"message\">' . lang("exp_gomacms_rating.thanks_for_voting") . '</div>");');

                return $response;
            } else {
                return $this->redirectback();
            }
        } else {
            if ($this->getRequest()->isJSResponse()) {
                $response = new AjaxResponse;
                $response->exec("alert('" . lang("exp_gomacms_rating.already_rated") . "');");

                return $response;
            } else {
                GlobalSessionManager::globalSession()->set("rating_message." . $name, lang("exp_gomacms_rating.already_rated"));
                return $this->redirectback();
            }
        }
    }
}
