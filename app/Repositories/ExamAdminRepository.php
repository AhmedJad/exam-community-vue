<?php

namespace App\Repositories;

use App\Models\Exam;

class ExamAdminRepository
{
    public function getExams($userId,$text,$folderId,$pageSize)
    {
        if ($pageSize) {
            return Exam::where("title", "like", "%$text%")->orderBy("id", "desc")
                ->when($folderId, function ($q) use($folderId) {
                    $q->where("folder_id", $folderId);
                }, function ($q) {
                    $q->whereNull("folder_id");
                })
                ->where("user_id", $userId)
                ->paginate($pageSize);
        }
        return Exam::get();
    }
    public function getChildren($parentId)
    {
        return Exam::where("exam_id", $parentId)->get();
    }
    public function create($exam)
    {
        return Exam::create($exam);
    }
    public function rename($exam)
    {
        $exam = Exam::where("id", $exam["id"])->update(["title" => $exam["title"]]);
    }
    public function editExam($exam)
    {
        Exam::where("id", $exam["id"])->update($exam);
    }
    public function delete($id)
    {
        Exam::where("id", $id)->delete();
    }
}
