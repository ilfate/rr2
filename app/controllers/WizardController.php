<?php

use Illuminate\Support\Facades\Redirect;

class WizardController extends BaseController {

    /** @var BattleWizard */
    protected $battleWizardModel;
    /** @var Wizard */
    protected $wizardModel;
    /** @var BattleMap */
    protected $battleMapModel;

    public function __construct()
    {
        $this->battleWizardModel = new BattleWizard();
        $this->wizardModel       = new Wizard();
        $this->battleMapModel    = new BattleMap();
    }

    /**
     * @param $id
     *
     * @return \Illuminate\View\View
     */
    public function getOverview($id)
	{
        $wizard = $this->wizardModel->findOrFail($id);
        return View::make('wizard.overview')->with('wizard', $wizard);
	}

    /**
     * creating wizards page
     *
     * @return \Illuminate\View\View
     */
    public function getCreateWizard()
    {
        $wizardsConfig = Config::get('wizards.wizards');
        return View::make('wizard.createWizard')->with('wizardsConfig', $wizardsConfig);
    }

    /**
     * Coming from form to create Wizard
     *
     * @return mixed
     */
    public function postCreateWizard()
    {
        $wizardTypes = Config::get('wizards.types');
        $input = Input::all();
        $rules = array(
            'name' => 'required|alpha_num|between:4,18',
            'type' => 'required|in:' . implode(',', $wizardTypes)
        );

        $validator = Validator::make($input, $rules);
        if ($validator->passes())
        {

            $wizard = new Wizard();
            $data = array(
                'user_id' => Auth::user()->id,
                'name' => $input['name'],
                'class' => $input['type'],
                'data' => ''
            );
            $wizard->create($data)->with('message', 'Congratulations now you have a wizard!');

            return Redirect::to('home');
        }
        return Redirect::to('wizard/create-wizard')
            ->withInput()
            ->withErrors($validator)
            ->with('message', 'There were validation errors.');
    }

    /**
     * @param $battleMapId
     * @param $wizardId
     */
    public function postPortal($battleMapId, $wizardId)
    {
        // here we are checking are we able to use this map and this wizard
        $wizard    = $this->wizardModel->where('id', '=', $wizardId)->where('user_id', '=', Auth::user()->id)->get();
        $battleMap = $this->battleMapModel->where('id', '=', $battleMapId)->where('active', '=', 1)->get();
        if (!$wizard || !$battleMap) {
            Redirect::to('home')->with('message', 'This action can not be performed');
        }

        $this->battleWizardModel->create(array(
            'battle_map_id' => $battleMapId,
            'wizard_id'     => $wizardId,
            'user_id'       => Auth::user()->id,
            'chunk_id'      => 0,
            'data'          => '',
        ));
        Redirect::to('game');
    }

}