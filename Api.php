<?php
/**
 * Created by PhpStorm.
 * User: davidamackenzie
 * Date: 9/29/18
 * Time: 2:40 PM
 */

class Api {
    private $resource;
    private $passedData;
    private $ip_address;
    private $session_info, $session;
    private $response;

    function __construct($resource,$passedData,$ip_address,$session_info) {
        $this->resource = $resource;
        $this->passedData = $passedData;
        $this->ip_address = $ip_address;
        $this->session_info = $session_info;
        $session = Session::getObjectById($this->session_info->id,Session::class);
        $this->session = $session;
        $this->response = new ApiResponse($resource);
    }

    public function getResponse() {
        error_log(json_encode($this->resource));
        switch ($this->resource[0]) {
            case 'signup-emails': $this->signupEmailsResource(); break;
            case 'accounts': $this->accountsResource(); break;
            case 'dimensions': $this->dimensionsResource(); break;
            case 'profiles': $this->profilesResource(); break;
            default: $this->setError('Unrecognized resource: '.$this->resource[0]); break;
        }
        return $this->response->getResponse();
    }

    private function setError($message) {
        $this->response->setStatus(0);
        $this->response->setErrorMessage($message);
    }

    private function authenticated() {
        if ($this->session !== false) {
            if ($this->session->getValue('session_hash') === $this->session_info->hash
                && $this->session->getValue('ip_address') === $this->ip_address
                && $this->session->getValue('active') === '1') {
                return true;
            }
        }
        $this->setError('Authentication invalid');
        return false;
    }

    private function signupEmailsResource() {
        switch ($this->resource[1]) {
            case 'get-all': $this->signupEmailsGetAll(); break;
            case 'save': $this->signupEmailSave(); break;
            case 'get-by-invite-code': $this->signupEmailGetByInviteCode(); break;
            default: $this->setError('Unrecognized resource: '.$this->resource[1]); break;
        }
    }

    private function accountsResource() {
        switch ($this->resource[1]) {
            case 'create': $this->accountCreate(); break;
            case 'signin': $this->accountSignin(); break;
            default: $this->setError('Unrecognized resource: '.$this->resource[1]); break;
        }
    }

    private function dimensionsResource() {
        switch ($this->resource[1]) {
            case 'get-all': $this->dimensionsGetAll(); break;
            case 'get-identities': $this->identitiesGetAll(); break;
            case 'save-identity': $this->saveIdentity(); break;
            case 'get-preferences': $this->preferencesGetAll(); break;
            case 'save-preference': $this->savePreference(); break;
            default: $this->setError('Unrecognized resource: '.$this->resource[1]); break;
        }
    }

    private function profilesResource() {
        switch ($this->resource[1]) {
            case 'get-matches': $this->getMatches(); break;
            case 'get-match': $this->getMatch(); break;
            case 'user-profile': $this->getUserProfile(); break;
            case 'user-profile-match': $this->getSelfAsMatch(); break;
            case 'save-profile': $this->saveProfile(); break;
            default: $this->setError('Unrecognized resource: '.$this->resource[1]); break;
        }
    }

    private function signupEmailsGetAll() {
        $emails = SignupEmail::getAllSignupEmails();
        $return_vals = [];
        foreach ($emails as $email) {
            $return_vals[] = $email->getValues();
        }
        $this->response->setStatus(1);
        $this->response->setData($return_vals);
    }

    private function signupEmailSave() {
        $email = $this->passedData;
        $emailObj = SignupEmail::getEmailObject($email);
        if (!$emailObj) {
            $emailObj = SignupEmail::newSignupEmail($email);
            $success = $emailObj->saveToDB(true);
            $this->response->setStatus($success ? 1 : 0);
            if ($success) {
                $emailObj->setInviteCode();
                $this->response->setData($emailObj->getValue('invite_code'));
            }
        } else {
            if (Account::getBySignupEmail($emailObj->getValue('id'))) {
                $this->setError('An account already exists for that email!');
            } else {
                $this->response->setData($emailObj->getValue('invite_code'));
                $this->setError('That email is already signed up!');
            }
        }
    }

    private function signupEmailGetByInviteCode() {
        $invite_code = $this->passedData;
        $emailObj = SignupEmail::getByInviteCode($invite_code);
        if ($emailObj) {
            $return_vals = $emailObj->getValues();
            $this->response->setStatus(1);
            $this->response->setData($return_vals);
        } else {
            $this->response->setErrorMessage('Cannot find signup email!');
        }
    }

    private function accountCreate() {
        $email = $this->passedData->email;
        $password_hash = $this->passedData->password_hash;
        $accountObj = Account::getAccountObj($email);
        if (!$accountObj) {
            $accountObj = Account::newAccount($email,$password_hash);
            $success = $accountObj->saveToDB();
            $emailObj = SignupEmail::getEmailObject($email);
            $accountObj->setValue('signupEmail_id',$emailObj->getValue('id'),true);
            $profileObj = Profile::newProfie($accountObj);
            $initial_name = explode('@',$email)[0];
            $profileObj->setValue(Profile::getColumns()['preferred_name']['name'],$initial_name);
            $profileObj->saveToDB();
            $profile_identities = Identity::getAllIdentities($profileObj->getValue('id'));
            $profile_preferences = Preference::getAllPreferences($profileObj->getValue('id'));
            $this->response->setData(['id'=> $accountObj->getValue('id')]);
            $this->response->setStatus($success ? 1 : 0);
        } else {
            $this->setError('Account already exists');
        }
    }

    private function accountSignin() {
        $email = $this->passedData->email;
        $password_hash = $this->passedData->password_hash;
        $accountObj = Account::getAccountObj($email);
        if ($accountObj) {
            $authentic = $accountObj->authenticate($password_hash);
            $this->response->setStatus($authentic ? 1 : 0);
            if (!$authentic) {
                $this->setError('Incorrect password');
            } else {
                $profileObj = Profile::getProfileByAccount($accountObj);
                $values = [
                    'account' => $accountObj->getValue('id'),
                    'profile' => $profileObj->getValue('id'),
                    'ip_address' => $this->ip_address,
                    'session_hash' => md5($accountObj->getValue('email').$this->ip_address.time()),
                    'active' => true
                    ];
                //error_log(json_encode($profileObj->getValues()));
                $sessionObj = new Session($values, false);
                $sessionObj->saveToDB();
                $this->response->setData(
                    ['session_id'=>$sessionObj->getValue('id'),
                        'session_hash'=>$sessionObj->getValue('session_hash'),
                        'userprofile'=>$profileObj->getValues()]);
            }
        }
    }

    private function dimensionsGetAll() {
        $dimensions_objs = Dimension::getDimensions();
        $dimension_categories_objs = DimensionCategory::getDimensionCategories();
        $dimensions = [];
        $dimension_categories = [];
        foreach ($dimensions_objs as $dim) {
            $dimensions[] = $dim->getValues();
        }
        foreach ($dimension_categories_objs as $dim_cat) {
            $dimension_categories[] = $dim_cat->getValues();
        }
        $this->response->setStatus(1);
        $this->response->setData(['dimensions'=>$dimensions,'dimension_categories'=>$dimension_categories]);
    }

    private function identitiesGetAll() {
        if (!$this->authenticated()) {
            return false;
        }
        $dimensions_objs = Dimension::getDimensions();
        $dimension_categories_objs = DimensionCategory::getDimensionCategories();
        $dimension_categories = [];
        foreach ($dimension_categories_objs as $dim_cat) {
            $dimension_categories[] = $dim_cat->getValues();
        }
        $identity_objs = Identity::getAllIdentities($this->session->getValue('profile'),$dimensions_objs);
        $identities = [];
        foreach ($identity_objs as $id_obj) {
            $identities[] = $id_obj->getValues(true);
        }
        $dimensions = [];
        foreach ($dimensions_objs as $dim) {
            $dimensions[] = $dim->getValues();
        }
        $this->response->setStatus(1);
        $this->response->setData(['identities'=>$identities,'dimensions'=>$dimensions,'dimension_categories'=>$dimension_categories]);
    }

    private function saveIdentity() {
        if (!$this->authenticated()) {
            return false;
        }
        $identity = Identity::getObjectById($this->passedData->id,Identity::class);
        foreach ($this->passedData as $column => $datum) {
            $identity->setValue($column,$datum,true);
        }
    }

    private function preferencesGetAll() {
        if (!$this->authenticated()) {
            return false;
        }
        $dimensions_objs = Dimension::getDimensions();
        $dimension_categories_objs = DimensionCategory::getDimensionCategories();
        $dimension_categories = [];
        foreach ($dimension_categories_objs as $dim_cat) {
            $dimension_categories[] = $dim_cat->getValues();
        }
        $preference_objs = Preference::getAllPreferences($this->session->getValue('profile'),$dimensions_objs);
        $preferences = [];
        foreach ($preference_objs as $pref_obj) {
            $preferences[] = $pref_obj->getValues(true);
        }
        $dimensions = [];
        foreach ($dimensions_objs as $dim) {
            $dimensions[] = $dim->getValues();
        }
        $this->response->setStatus(1);
        $this->response->setData(['preferences'=>$preferences,'dimensions'=>$dimensions,'dimension_categories'=>$dimension_categories]);
    }

    private function savePreference() {
        if (!$this->authenticated()) {
            return false;
        }
        $preference = Preference::getObjectById($this->passedData->id,Preference::class);
        foreach ($this->passedData as $column => $datum) {
            $preference->setValue($column,$datum,true);
        }
    }

    private function getMatches() {
        if (!$this->authenticated()) {
            return false;
        }
        $returnMatches = [];
        $matches = Profile::getMatches();
        $dimensions_objs = Dimension::getDimensions();
        $ids_file = fopen("python/ids.csv","w");
        $preferences_file = fopen("python/preferences.csv","w");
        $identities_file = fopen("python/identities.csv","w");
        foreach ($matches as $match) {
            fputcsv($ids_file,[$match->getValue('id')]);
            $returnMatch = $match->getValues();
            if (!$returnMatch['picture_file']) {
                $returnMatch['picture_file'] = 'vitruvian-man.jpg';
            }
            $identities = Identity::getAllIdentities($match->getValue('id'),$dimensions_objs);
            fputcsv($identities_file, array_map(function($obj){ return $obj->vectorValue(); }, $identities));
            usort($identities, function ($a, $b) {
                return $a->vectorValue() - $b->vectorValue();
            });
            $top_identities = [];
            for ($i = 0; $i < 3; $i++) {
                $id = $identities[count($identities) - $i - 1];
                if ($id->vectorValue() != 0) {
                    $top_identities[] = $id->getDimension()->getValues(true);

                }
            }
            $returnMatch['top_identities'] = $top_identities;
            $preferences = Preference::getAllPreferences($match->getValue('id'),$dimensions_objs);
            fputcsv($preferences_file, array_map(function($obj){ return $obj->vectorValue(); }, $preferences));
            usort($preferences, function ($a, $b) {
                return $a->vectorValue() - $b->vectorValue();
            });
            $top_preferences = [];
            for ($i = 0; $i < 3; $i++) {
                $pref = $preferences[count($preferences) - $i - 1];
                if ($pref->vectorValue() != 0) {
                    $top_preferences[] = $pref->getDimension()->getValues(true);
                }
            }
            $returnMatch['top_preferences'] = $top_preferences;
            if ($this->session->getValue('profile') != $match->getValue('id')) {
                $returnMatches[] = $returnMatch;
            }
        }
        fclose($ids_file);
        fclose($preferences_file);
        fclose($identities_file);
        $cosines = shell_exec('python python/match_cosine.py '.$this->session->getValue('profile'));
        $cosines = explode("\n",$cosines);
        $cosines = array_map(function($obj){ return json_decode($obj); }, $cosines);
        $match_scores = [];
        for ($i=0; $i<count($cosines[0]); $i++) {
            $match_scores[$cosines[0][$i]] = ['myprefs'=>$cosines[1][$i], 'theirprefs'=>$cosines[2][$i]];
        }
        //$parsed_cosines = json_decode($cosines);
        error_log(json_encode($cosines));
        $this->response->setStatus(1);
        $this->response->setData(['matches'=>$returnMatches, 'cosines'=>json_encode($match_scores)]);
    }

    private function getMatch($profile_id=false) {
        if (!$profile_id) {
            $profile_id = $this->passedData;
        }
        if (!$this->authenticated()) {
            return false;
        }
        $dimensions = Dimension::getDimensions();
        $profile = Profile::getObjectById($this->session->getValue('profile'),Profile::class);
        $match = Profile::getObjectById($profile_id,Profile::class);
        $match_identities = Identity::getAllIdentities($match->getValue('id'),$dimensions);
        usort($match_identities,function($a,$b) {
            return $b->vectorValue() - $a->vectorValue();
        });
        $top_identities = [];
        $match_preferences = Preference::getAllPreferences($match->getValue('id'),$dimensions);
        usort($match_preferences,function($a,$b) {
            return $b->vectorValue() - $a->vectorValue();
        });
        $top_preferences = [];
        $profile_identities = Identity::getAllIdentities($profile->getValue('id'),$dimensions);
        $profile_preferences = Preference::getAllPreferences($profile->getValue('id'),$dimensions);
        $youLikeThem = [];
        $theyLikeYou = [];
        $youNotLikeThem = [];
        foreach ($match_identities as $them) {
            if (count($top_identities)<3) {
                $top_identities[] = $them->getDimension()->getValues(true);
            }
            foreach ($profile_preferences as $you) {
                if ($you->getValue('dimension_id') == $them->getValue('dimension_id')) {
                    break;
                }
            }
            $match_value = $you->vectorValue() * $them->vectorValue();
            if ($match_value > 0) {
                $youLikeThem[] = ['dimension' => $you->getDimension()->getValues(true),
                    'match_value' => $match_value,
                    'not' => $them->vectorValue()<0];
            } else if ($match_value < 0) {
                $youNotLikeThem[] = ['dimension' => $you->getDimension()->getValues(true),
                    'match_value' => $match_value,
                    'not' => $them->vectorValue()<0];
            }
        }
        foreach ($profile_identities as $you) {
            foreach ($match_preferences as $them) {
                if (count($top_preferences)<3) {
                    $top_preferences[] = $them->getDimension()->getValues(true);
                }
                if ($you->getValue('dimension_id') == $them->getValue('dimension_id')) {
                    break;
                }
            }
            $match_value = $you->vectorValue() * $them->vectorValue();
            if ($match_value > 0) {
                $theyLikeYou[] = ['dimension' => $you->getDimension()->getValues(true),
                    'match_value' => $match_value,
                    'not' => $you->vectorValue()<0];
            }
        }
        // Calculate age
        $date = new DateTime($match->getValue('birthday'));
        $now = new DateTime();
        $interval = $now->diff($date);
        $age = $interval->y;
        $match_data = $match->getValues();
        $match_data['age'] = $age;
        if (!$match_data['picture_file']) {
            $match_data['picture_file'] = 'vitruvian-man.jpg';
        }
        $resp_data = ['match'=>$match_data,
            'top_identities' => $top_identities,
            'top_preferences' => $top_preferences,
            'theyLikeYou' => $theyLikeYou,
            'youLikeThem' => $youLikeThem,
            'youNotLikeThem' => $youNotLikeThem];
        $this->response->setData($resp_data);
        $this->response->setStatus(1);
    }

    private function getSelfAsMatch() {
        $this->getMatch($this->session->getValue('profile'));
    }

    private function getUserProfile() {
        if (!$this->authenticated()) {
            return false;
        }
        $profile = Profile::getObjectById($this->session->getValue('profile'),Profile::class);
        $this->response->setStatus(1);
        $this->response->setData($profile->getValues());
    }

    private function saveProfile() {
        if (!$this->authenticated()) {
            return false;
        }
        $profile = Profile::getObjectById($this->session->getValue('profile'),Profile::class);
        foreach ($this->passedData as $key => $val) {
            $profile->setValue($key,$val,true);
        }
        $this->getSelfAsMatch();
    }

}