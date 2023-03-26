<?php

namespace App\Http\Controllers;

use App\Http\Requests\EditExamRequest;
use App\Http\Requests\StoreExamRequest;
use App\Repositories\ExamAdminRepository;
use Tymon\JWTAuth\Facades\JWTAuth;

class ExamAdminController extends Controller
{
    private $examAdminRepository;
    public function __construct(ExamAdminRepository $examAdminRepository)
    {
        $this->middleware("auth");
        $this->examAdminRepository = $examAdminRepository;
    }
    public function index()
    {
        $userId = JWTAuth::parseToken()->getPayload()->get("sub");
        $text = isset(request()->text) ? request()->text : '';
        return $this->examAdminRepository->getExams(
            $userId,
            $text,
            request()->folder_id,
            request()->page_size
        );
    }
    //This method for init folder or exam
    public function store(StoreExamRequest $request)
    {
        $userId = JWTAuth::parseToken()->getPayload()->get("sub");
        $input = $request->validated();
        $input["user_id"] = $userId;
        return $this->examAdminRepository->create($input);
    }
    public function update(EditExamRequest $request)
    {
        $this->examAdminRepository->editExam($request->input());
    }
    public function delete($id)
    {
        $this->examAdminRepository->delete($id);
    }
}
