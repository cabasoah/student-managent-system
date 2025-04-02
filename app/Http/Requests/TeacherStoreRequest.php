<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\LecturerInvite;

class TeacherStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Allow authenticated users with permission
        if (auth()->check()) {
            return auth()->user()->can('create users');
        }
     
        // For non-authenticated users, validate the invite token
        return $this->validateInviteToken();

    }

    /**
     * Validate the invitation token for guest access
     */
    protected function validateInviteToken()
    {
        $token = $this->input('invite_token') ?? session('lecturer_invite');
       
        if (!$token) {
            return false;
        }
    
        return LecturerInvite::where('token', $token)
            ->where('used', true)
            ->where('expires_at', '>=', now())
            ->exists();
    }
    
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'first_name'    => 'required|string',
            'last_name'     => 'required|string',
            'email'         => 'required|string|email|max:255|unique:users',
            'gender'        => 'required|string',
            'nationality'   => 'required|string',
            'phone'         => 'required|string',
            'address'       => 'required|string',
            'address2'      => 'nullable|string',
            'city'          => 'required|string',
            'zip'           => 'required|string',
            'photo'         => 'nullable|string',
            'password'      => 'required|string|min:8',
        ];
        return $rules;
    }
}
