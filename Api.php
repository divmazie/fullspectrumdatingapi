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
            case 'save-preferred-name': $this->savePreferredName(); break;
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
        foreach ($matches as $match) {
            $returnMatch = $match->getValues();
            $identities = Identity::getAllIdentities($match->getValue('id'));
            usort($identities,function($a,$b) {
                return $a->vectorValue() - $b->vectorValue();
            });
            $top_identities = [];
            for ($i=0; $i<3; $i++) {
                $top_identities[] = $identities[count($identities)-$i-1]->getDimension()->getValues(true);
            }
            $returnMatch['top_identities'] = $top_identities;
            $preferences = Preference::getAllPreferences($match->getValue('id'));
            usort($preferences,function($a,$b) {
                return $a->vectorValue() - $b->vectorValue();
            });
            $top_preferences = [];
            for ($i=0; $i<3; $i++) {
                $top_preferences[] = $preferences[count($preferences)-$i-1]->getDimension()->getValues(true);
            }
            $returnMatch['top_preferences'] = $top_preferences;
            $returnMatches[] = $returnMatch;
        }
        $this->response->setStatus(1);
        $this->response->setData(['matches'=>$returnMatches]);
    }

    private function getMatch() {
        if (!$this->authenticated()) {
            return false;
        }
        $dimensions = Dimension::getDimensions();
        $profile = Profile::getObjectById($this->session->getValue('profile'),Profile::class);
        $match = Profile::getObjectById($this->passedData,Profile::class);
        $match_identities = Identity::getAllIdentities($match->getValue('id'),$dimensions);
        $match_preferences = Preference::getAllPreferences($match->getValue('id'),$dimensions);
        $profile_identities = Identity::getAllIdentities($profile->getValue('id'),$dimensions);
        $profile_preferences = Preference::getAllPreferences($profile->getValue('id'),$dimensions);
        $youLikeThem = [];
        $theyLikeYou = [];
        $youNotLikeThem = [];
        foreach ($match_identities as $them) {
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
        $this->response->setStatus(1);
        $resp_data = ['match'=>$match->getValues(),
            'theyLikeYou' => $theyLikeYou,
            'youLikeThem' => $youLikeThem,
            'youNotLikeThem' => $youNotLikeThem];
        $this->response->setData($resp_data);
    }

    private function getUserProfile() {
        if (!$this->authenticated()) {
            return false;
        }
        $profile = Profile::getObjectById($this->session->getValue('profile'),Profile::class);
        $this->response->setStatus(1);
        $this->response->setData($profile->getValues());
    }

    private function savePreferredName() {
        if (!$this->authenticated()) {
            return false;
        }
        $profile = Profile::getObjectById($this->session->getValue('profile'),Profile::class);
        $profile->setValue('preferred_name',$this->passedData,true);
        $this->response->setStatus(1);
        $this->response->setData($profile->getValues());
    }

}