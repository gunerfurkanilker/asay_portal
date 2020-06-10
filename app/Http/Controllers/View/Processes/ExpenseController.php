<?php

namespace App\Http\Controllers\View\Processes;

use App\Http\Controllers\View\AsayController;
use Illuminate\Http\Request;

class ExpenseController extends AsayController
{
    public function expense_list(Request $request,$status="")
    {
        $client = new \GuzzleHttp\Client();
        $data["user"] = $request->session()->get("user");
        $token = $data["user"]->token;
        $data["request"] = json_decode($client->get($this->api_url."processes/expense/list", ["query"=>["token"=>$token,"status"=>$status]])->getBody())->data;

        $data["menu"] = "processes";
        $data["SubheaderMenu"] = "expense_list";
        $data["Title"] = "Masraflarım";
        return view("processes.expenses.user.lists",$data);
    }

    public function expense_add()
    {
        $data["menu"] = "processes";
        $data["SubheaderMenu"] = "expense_list";
        $data["Title"] = "Masraf Ekleme Formu";
        $data["api_url"] = $this->api_url;
        return view("processes.expenses.user.expense_add",$data);
    }

    public function expense_add_post(Request $request)
    {
        $client = new \GuzzleHttp\Client();
        $token = $request->session()->get("user")->token;
        $post["type"]                = $request->input("type");
        $post["NAME"]                = $request->input("NAME");
        $post["EXPENSE_TYPE"]        = $request->input("EXPENSE_TYPE");
        $post["EXPENSE_TYPE_VALUE"]  = $request->input("EXPENSE_TYPE_VALUE");
        $post["MASRAF_SEKLI"]        = $request->input("MASRAF_SEKLI");
        $post["CONTENT"]             = $request->input("CONTENT");
        $post["expense_id"]          = $request->input("expense_id");
        $post["expense_id"]          = $request->input("expense_id");
        $post["token"]                  = $token;
        $response = json_decode($client->post($this->api_url."processes/expense/expenseSave", ["form_params"=> $post])->getBody());
        if($response->status==true)
        {
            if($response->type=="kaydet")
                return redirect()->route("expense_view",["expense_id"=>$response->data->expense_id])->with(["status"=>false,"message"=>"Masraf Onaya Gönderildi."]);
            else
                return redirect()->route("expense_list")->with(["status"=>false,"message"=>"Masraf Onaya Gönderildi."]);
        }
        else
        {
            return back()->with(["status"=>false,"message"=>$response->message]);
        }
    }
    public function expense_view(Request $request,$expense_id)
    {
        $client = new \GuzzleHttp\Client();
        $token = $request->session()->get("user")->token;
        $expense = json_decode($client->get($this->api_url."processes/expense/getExpense", ["query"=>["token"=>$token,"expense_id"=>$expense_id]])->getBody());
        if($expense->status==true)
        {
            $data["expense"] = $expense->data;
        }
        else
        {
            return redirect(route("expense_list"));
        }
        $data["expenseDocuments"] = json_decode($client->get($this->api_url."processes/expense/getExpenseDocuments", ["query"=>["token"=>$token,"expense_id"=>$expense_id]])->getBody())->data;
        $data["parabirimleri"] = json_decode($client->get($this->api_url."processes/expense/getParaBirimleri", ["query"=>["token"=>$token]])->getBody())->data;

        $data["menu"] = "processes";
        $data["SubheaderMenu"] = "expense_list";
        $data["Title"] = "Masraf Formu";
        $data["api_url"] = $this->api_url;
        return view("processes.expenses.user.expense_view",$data);
    }
    public function expense_add_document(Request $request,$expense_id)
    {
        $token = $request->session()->get("user")->token;
        $client = new \GuzzleHttp\Client();
        $data["muhasebeGiderHesaplari"] = json_decode($client->get($this->api_url."processes/expense/getMuhasebeGiderHesaplari", ["query"=>["token"=>$token]])->getBody())->data;


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
