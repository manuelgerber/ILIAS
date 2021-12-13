<?php
declare(strict_types = 1);

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

namespace ILIAS\Survey\Mode\IndividualFeedback;

use \ILIAS\Survey\Mode;
use ILIAS\Survey\InternalUIService;

/**
 * Interface for modes
 * @author Alexander Killing <killing@leifos.de>
 */
class UIModifier extends Mode\AbstractUIModifier
{
    /**
     * @inheritDoc
     */
    public function getSurveySettingsGeneral(
        \ilObjSurvey $survey
    ) : array {
        $items = [];

        return $items;
    }

    /**
     * @inheritDoc
     */
    public function getSurveySettingsResults(
        \ilObjSurvey $survey,
        InternalUIService $ui_service
    ) : array {
        $items = [];
        $lng = $ui_service->lng();

        $ts_results = new \ilRadioGroupInputGUI($lng->txt("survey_360_results"), "ts_res");
        $ts_results->setValue((string) $survey->get360Results());

        $option = new \ilRadioOption($lng->txt("survey_360_results_none"), (string) \ilObjSurvey::RESULTS_360_NONE);
        $option->setInfo($lng->txt("survey_360_results_none_info"));
        $ts_results->addOption($option);

        $option = new \ilRadioOption(
            $lng->txt("survey_360_results_own"),
            (string) \ilObjSurvey::RESULTS_360_OWN
        );
        $option->setInfo($lng->txt("survey_360_results_own_info"));
        $ts_results->addOption($option);

        $items[] = $ts_results;

        return $items;
    }

    /**
     * @inheritDoc
     */
    public function getSurveySettingsReminderTargets(
        \ilObjSurvey $survey,
        InternalUIService $ui_service
    ) : array {
        $items = [];
        $lng = $ui_service->lng();

        // remind appraisees
        $cb = new \ilCheckboxInputGUI($lng->txt("survey_notification_target_group"), "remind_appraisees");
        $cb->setOptionTitle($lng->txt("survey_360_appraisees"));
        $cb->setInfo($lng->txt("survey_360_appraisees_remind_info"));
        $cb->setValue("1");
        $cb->setChecked(in_array(
            $survey->getReminderTarget(),
            array(\ilObjSurvey::NOTIFICATION_APPRAISEES, \ilObjSurvey::NOTIFICATION_APPRAISEES_AND_RATERS)
        ));
        $items[] = $cb;

        // remind raters
        $cb = new \ilCheckboxInputGUI("", "remind_raters");
        $cb->setOptionTitle($lng->txt("survey_360_raters"));
        $cb->setInfo($lng->txt("survey_360_raters_remind_info"));
        $cb->setValue("1");
        $cb->setChecked(in_array(
            $survey->getReminderTarget(),
            array(\ilObjSurvey::NOTIFICATION_RATERS, \ilObjSurvey::NOTIFICATION_APPRAISEES_AND_RATERS)
        ));
        $items[] = $cb;

        return $items;
    }

    /**
     * @inheritDoc
     */
    public function setValuesFromForm(
        \ilObjSurvey $survey,
        \ilPropertyFormGUI $form
    ) : void {
        if ($form->getInput("remind_appraisees") && $form->getInput("remind_raters")) {
            $survey->setReminderTarget(\ilObjSurvey::NOTIFICATION_APPRAISEES_AND_RATERS);
        } elseif ($form->getInput("remind_appraisees")) {
            $survey->setReminderTarget(\ilObjSurvey::NOTIFICATION_APPRAISEES);
        } elseif ($form->getInput("remind_raters")) {
            $survey->setReminderTarget(\ilObjSurvey::NOTIFICATION_RATERS);
        } else {
            $survey->setReminderTarget(0);
        }

        $survey->set360Results((int) $form->getInput("ts_res"));
    }


    public function setResultsDetailToolbar(
        \ilObjSurvey $survey,
        \ilToolbarGUI $toolbar,
        int $user_id
    ) : void {
        $gui = $this->service->ui();
        $lng = $gui->lng();

        $this->addApprSelectionToToolbar(
            $survey,
            $toolbar,
            $user_id
        );

        $this->addExportAndPrintButton(
            $toolbar,
            true
        );
    }

    public function setResultsCompetenceToolbar(
        \ilObjSurvey $survey,
        \ilToolbarGUI $toolbar,
        int $user_id
    ) : void {
        $gui = $this->service->ui();
        $lng = $gui->lng();

        $this->addApprSelectionToToolbar(
            $survey,
            $toolbar,
            $user_id
        );

        $this->addRaterSelectionToToolbar(
            $survey,
            $toolbar,
            $user_id
        );
    }

    /**
     * Add rater selection to toolbar
     */
    public function addRaterSelectionToToolbar(
        \ilObjSurvey $survey,
        \ilToolbarGUI $toolbar,
        int $user_id
    ) {
        $lng = $this->service->ui()->lng();
        $ctrl = $this->service->ui()->ctrl();
        $req = $this->service->ui()->evaluation($survey)->request();

        $evaluation_manager = $this->service->domain()->evaluation(
            $survey,
            $user_id,
            $req->getAppraiseeId(),
            $req->getRaterId()
        );


        if (!$evaluation_manager->isMultiParticipantsView()) {
            $raters = $evaluation_manager->getSelectableRaters();

            if (count($raters) > 0) {
                $options = [];
                $options["-"] = $lng->txt("svy_all_raters");
                foreach ($raters as $rater) {
                    $options[$rater["user_id"]] = $rater["name"];
                }

                $rat = new \ilSelectInputGUI($lng->txt("svy_rater"), "rater_id");
                $rat->setOptions($options);
                $rat->setValue($evaluation_manager->getCurrentRater());
                $toolbar->addInputItem($rat, true);

                $button = \ilSubmitButton::getInstance();
                $button->setCaption("svy_select_rater");
                $button->setCommand($ctrl->getCmd());
                $toolbar->addButtonInstance($button);

                $toolbar->addSeparator();
            }
        }
    }


    protected function getPanelChart(
        \ILIAS\Survey\Evaluation\EvaluationGUIRequest $request,
        \SurveyQuestionEvaluation $a_eval
    ) : string {
        return "";
    }

    protected function getPanelText(
        \ILIAS\Survey\Evaluation\EvaluationGUIRequest $request,
        \SurveyQuestionEvaluation $a_eval,
        \ilSurveyEvaluationResults $question_res
    ) : string {
        return "";
    }

    protected function getPanelTable(
        array $participants,
        \ILIAS\Survey\Evaluation\EvaluationGUIRequest $request,
        \SurveyQuestionEvaluation $a_eval
    ) : string {
        $a_results = $a_eval->getResults();
        $lng = $this->service->ui()->lng();
        $matrix = false;
        if (is_array($a_results)) {
            $answers = $a_results[0][1]->getAnswers();
            $q = $a_results[0][1]->getQuestion();
            $matrix = true;
        } else {
            $answers = $a_results->getAnswers();
            $q = $a_results->getQuestion();
        }
        // SurveySingleChoiceQuestion
        if (!in_array($q->getQuestionType(), [
            "SurveySingleChoiceQuestion",
            "SurveyMultipleChoiceQuestion",
            "SurveyMetricQuestion",
            "SurveyTextQuestion"
        ])) {
            //var_dump($q->getQuestionType());
            //var_dump($answers);
            //exit;
        }


        \ilDatePresentation::setUseRelativeDates(false);

        $a_tpl = new \ilTemplate("tpl.svy_results_details_table.html", true, true, "Modules/Survey/Evaluation");

        // table
        $ret = "";
        if ($request->getShowTable()) {
            if (!$matrix) {

                // rater
                $a_tpl->setCurrentBlock("grid_col_header_bl");
                $a_tpl->setVariable("COL_HEADER", $lng->txt("svy_rater"));
                $a_tpl->parseCurrentBlock();

                // date
                $a_tpl->setCurrentBlock("grid_col_header_bl");
                $a_tpl->setVariable("COL_HEADER", $lng->txt("date"));
                $a_tpl->parseCurrentBlock();

                // answer
                $a_tpl->setCurrentBlock("grid_col_header_bl");
                $a_tpl->setVariable("COL_HEADER", $lng->txt("answers"));
                $a_tpl->parseCurrentBlock();

                $condensed_answers = [];
                foreach ($answers as $answer) {
                    $condensed_answers[$answer->active_id]["tstamp"] = $answer->tstamp;
                    $condensed_answers[$answer->active_id]["active_id"] = $answer->active_id;
                    // this moves the original multiple answers items of muliple choice question into one array
                    $condensed_answers[$answer->active_id]["value"][] = $answer->value;
                    $condensed_answers[$answer->active_id]["text"] = $answer->text;
                }

                /** @var $answer \ilSurveyEvaluationResultsAnswer */
                foreach ($condensed_answers as $answer) {
                    // rater
                    $a_tpl->setCurrentBlock("grid_col_bl");
                    $a_tpl->setVariable("COL_CAPTION", " ");
                    $a_tpl->parseCurrentBlock();

                    // rater
                    $participant = $this->getParticipantByActiveId($participants, $answer["active_id"]);
                    $part_caption = "";
                    if ($participant) {
                        $part_caption = $this->getCaptionForParticipant($participant);
                    }
                    $a_tpl->setCurrentBlock("grid_col_bl");
                    $a_tpl->setVariable("COL_CAPTION", $part_caption);
                    $a_tpl->parseCurrentBlock();

                    // date
                    $a_tpl->setCurrentBlock("grid_col_bl");
                    $date = new \ilDate($answer["tstamp"], IL_CAL_UNIX);
                    $a_tpl->setVariable(
                        "COL_CAPTION",
                        \ilDatePresentation::formatDate($date)
                    );
                    $a_tpl->parseCurrentBlock();

                    // answer
                    $a_tpl->setCurrentBlock("grid_col_bl");
                    if ($matrix) {
                        $a_tpl->setVariable("COL_CAPTION", "-");
                    } else {
                        if ($q->getQuestionType() == "SurveyTextQuestion") {
                            $a_tpl->setVariable(
                                "COL_CAPTION",
                                $a_results->getScaleText($answer["text"])
                            );
                        } else {
                            $scale_texts = array_map(function ($v) use ($a_results) {
                                return $a_results->getScaleText($v);
                            }, $answer["value"]);
                            $a_tpl->setVariable(
                                "COL_CAPTION",
                                implode(", ", $scale_texts)
                            );
                        }
                    }
                    $a_tpl->parseCurrentBlock();

                    $a_tpl->touchBlock("grid_row_bl");
                }
                $ret = $a_tpl->get();
            } else {

                /** @var $answer \ilSurveyEvaluationResultsAnswer */
                foreach ($answers as $answer) {

                    /** @var $q \SurveyMatrixQuestion */

                    $cats = $q->getColumns();
                    foreach ($cats->getCategories() as $cat) {
                        $a_tpl->touchBlock("grid_col_head_center");
                        $a_tpl->setCurrentBlock("grid_col_header_bl");
                        $a_tpl->setVariable("COL_HEADER", $cat->title);
                        $a_tpl->parseCurrentBlock();
                    }

                    $cats_rows = $q->getRows();

                    reset($a_results);
                    foreach ($cats_rows->getCategories() as $cat) {
                        $a_tpl->setCurrentBlock("grid_col_bl");
                        $a_tpl->setVariable("COL_CAPTION", $cat->title);
                        $a_tpl->parseCurrentBlock();

                        $r = current($a_results);
                        $row_answers = $r[1]->getAnswers();
                        $user_answers = null;
                        foreach ($row_answers as $ra) {
                            if ($ra->active_id == $answer->active_id) {
                                $user_answers = $ra;
                            }
                        }

                        foreach ($cats->getCategories() as $catr) {
                            if ($user_answers && $user_answers->value == $catr->scale) {
                                $a_tpl->touchBlock("grid_col_center");
                                $a_tpl->setCurrentBlock("grid_col_bl");
                                $a_tpl->setVariable("COL_CAPTION", "X");
                            } else {
                                $a_tpl->setCurrentBlock("grid_col_bl");
                                $a_tpl->setVariable("COL_CAPTION", " ");
                            }

                            $a_tpl->parseCurrentBlock();
                        }

                        $a_tpl->touchBlock("grid_row_bl");
                        next($a_results);
                    }

                    // rater
                    $participant = $this->getParticipantByActiveId($participants, $answer->active_id);
                    $part_caption = "";
                    if ($participant) {
                        $part_caption = $participant["sortname"];
                    }
                    $date = new \ilDate($answer->tstamp, IL_CAL_UNIX);
                    $part_caption .= ", " . \ilDatePresentation::formatDate($date);

                    $a_tpl->setVariable("HEADER", $part_caption);
                    $ret .= $a_tpl->get();
                    $a_tpl = new \ilTemplate("tpl.svy_results_details_table.html", true, true, "Modules/Survey/Evaluation");
                }
            }
        }
        return $ret;
    }

    protected function getParticipantByActiveId(array $participants, int $active_id) : ?array
    {
        foreach ($participants as $part) {
            if ((int) $part["active_id"] == $active_id) {
                return $part;
            }
        }
        return null;
    }

    protected function getCaptionForParticipant($part_array)
    {
        return $part_array["sortname"];
    }
}