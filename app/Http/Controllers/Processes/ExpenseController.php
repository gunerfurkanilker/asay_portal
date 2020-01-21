<?php

namespace App\Http\Controllers\Processes;

use App\Http\Controllers\AsayController;
use Illuminate\Http\Request;

class ExpenseController extends AsayController
{
    public function expense_list(Request $request,$status="")
    {
        $client = new \GuzzleHttp\Client();
        $token = $request->session()->get("user")->token;
        $data["request"] = json_decode($client->get($this->api_url."processes/expense/list", ["query"=>["token"=>$token,"status"=>$status]])->getBody())->data;
        $data["menu"] = "processes";
        $data["SubheaderMenu"] = "expense_list";
        $data["Title"] = "Masraflarım";
        return view("processes.expenses.user.lists",$data);
    }

    public function expense_add(Request $request)
    {
        $data["menu"] = "processes";
        $data["SubheaderMenu"] = "expense_list";
        $data["Title"] = "Masraf Ekleme Formu";
        return view("processes.expenses.user.expense_add",$data);
    }
    public function expense_view($expense_id)
    {
        $data["menu"] = "processes";
        $data["SubheaderMenu"] = "expense_list";
        $data["Title"] = "Masraf Formu";
        return view("processes.expenses.user.expense_view",$data);
    }
    public function expense_add_document($expense_id)
    {
        $data["menu"] = "processes";
        $data["SubheaderMenu"] = "expense_list";
        $data["Title"] = "Masraf Belgesi";
        return view("processes.expenses.user.expense_add_document",$data);
    }

    public function expense_print($expense_id)
    {
        $data["menu"] = "processes";
        $data["SubheaderMenu"] = "expense_list";
        $data["Title"] = "Masraf Belgesi";
        return view("processes.expenses.user.expense_print",$data);
    }

    public function expense_manager_list()
    {
        $data["menu"] = "processes";
        $data["SubheaderMenu"] = "expense_manager_list";
        $data["Title"] = "Yönetici Onay Bekleyen Masraflar";
        return view("processes.expenses.manager.lists",$data);
    }
    public function expense_manager_view()
    {
        $data["menu"] = "processes";
        $data["SubheaderMenu"] = "expense_manager_list";
        $data["Title"] = "Masraf Formu";
        return view("processes.expenses.manager.expense_view",$data);
    }
    public function expense_document_pending()
    {
        $data["menu"] = "processes";
        $data["SubheaderMenu"] = "expense_manager_list";
        $data["Title"] = "Yönetici Onay Bekleyen Masraflar";
        return view("processes.expenses.manager.pending_document",$data);
    }
    public function expense_accounting_list()
    {
        $data["menu"] = "processes";
        $data["SubheaderMenu"] = "expense_accounting_list";
        $data["Title"] = "Muhasebe Onayı Bekleyen Masraflar";
        return view("processes.expenses.accounting.lists",$data);
    }
    public function expense_accounting_view()
    {
        $data["menu"] = "processes";
        $data["SubheaderMenu"] = "expense_accounting_list";
        $data["Title"] = "Masraf Formu";
        return view("processes.expenses.accounting.view",$data);
    }
    public function expense_accounting_document_pending()
    {
        $data["menu"] = "processes";
        $data["SubheaderMenu"] = "expense_accounting_list";
        $data["Title"] = "Masraf Belge Onay Formu";
        return view("processes.expenses.accounting.pending_document",$data);
    }

}
