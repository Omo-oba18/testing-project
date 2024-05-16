<?php

namespace App\Http\Controllers;

use App\Contact;
use App\Helpers\Country;
use App\Http\Resources\ContactResource;
use App\Services\AuthService;
use App\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ContactController extends Controller
{
    public function __construct(AuthService $authService)
    {
        parent::__construct($authService);
    }

    /**
     * List all contact
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function all(Request $request)
    {
        $user = \Auth::user()->user;
        // $user     = User::where('id',4)->first();
        $contacts = Contact::where('user_id', $user->id);

        if ($request->is_personal) {
            $contacts = $contacts->where(function ($q) use ($user) {
                $q->where('user_contact_id', '!=', $user->id)->orWhere('user_contact_id', null);
            });
        }
        $contacts = $contacts->orderBy('name')->get();
        //order by name
        $contacts = $contacts->sortBy(function ($contact, $key) {
            return $contact->name ?? optional($contact->userContact)->name;
        });
        $contacts->values()->all();

        ContactResource::withoutWrapping();

        return ContactResource::collection($contacts);
    }

    /**
     * Add contact
     *
     *
     * @return ContactResource|\Illuminate\Http\JsonResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Throwable
     */
    public function addContact(Request $request)
    {
        $user = \Auth::user()->user;

        $this->validate($request, [
            'phone_number' => ['required', 'string', 'max:20', Rule::unique('contacts')->where(function ($query) use ($user) {
                return $query->where('user_id', $user->id);
            })],
            'dial_code' => ['required', Rule::in(Country::getCountryDialCodes())],
            'name' => ['required', 'string', 'max:50'],
            'avatar' => ['nullable', 'image', 'mimes:jpeg,bmp,png,jpg,gif', 'max:'.Contact::AVATAR_MAXSIZE],
        ]);
        //1. phone number - unique on contacts table (validate above)
        //2. phone number - unique on contacts table with link contact user
        $existContactWithPhoneNumber = Contact::whereHas('userContact', function (Builder $query) use ($request) {
            $query->where('phone_number', $request->get('phone_number'));
        })->count();
        if ($existContactWithPhoneNumber > 0) {
            return response()->json(['message' => __('contact.error_phone_number_unique')], 422);
        }

        if ($request->get('phone_number') == $user->phone_number) {
            return response()->json(['message' => __('contact.error_own_phone_number')], 422);
        }

        //save data
        $contact = null;
        \DB::transaction(function () use ($request, $user, &$contact) {
            $data = $request->except('avatar');
            if ($request->hasFile('avatar')) {
                $data['avatar'] = Contact::uploadOrigin($request->file('avatar'), Contact::$subFolder);
            }
            $data['user_id'] = $user->id;
            //link contact user
            $contactUser = User::where('phone_number', $data['phone_number'])->where('dial_code', $data['dial_code'])->first();
            if ($contactUser) {
                $data['user_contact_id'] = $contactUser->id;
            }
            $contact = Contact::create($data);
        });
        ContactResource::withoutWrapping();

        return new ContactResource($contact);
    }

    /**
     * Add contact
     *
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Throwable
     */
    public function importContacts(Request $request)
    {
        $this->validate($request, [
            'contacts' => ['required', 'array', 'max:20'],
            'contacts.*.phone_number' => ['required', 'string', 'max:20'],
            'contacts.*.dial_code' => ['required', Rule::in(Country::getCountryDialCodes())],
            'contacts.*.name' => ['required', 'string', 'max:50'],
            'contacts.*.avatar' => [
                'nullable',
                'image',
                'mimes:jpeg,bmp,png,jpg,gif',
                'max:'.Contact::AVATAR_MAXSIZE,
            ],
        ]);
        \DB::transaction(function () use ($request) {
            $user = \Auth::user()->user;
            $contacts = $request->get('contacts');
            foreach ($contacts as $key => $c) {
                $phone = $c['phone_number'];
                //1. if have phone number or phone num = user phone num, pass it
                $existContact = Contact::where('user_id', $user->id)
                    ->where(function (Builder $query) use ($phone) {
                        $query->where('phone_number', $phone)
                            ->OrWhereHas('userContact', function (Builder $query) use ($phone) {
                                $query->where('phone_number', $phone);
                            });
                    });
                if ($user->phone_number == $phone || $existContact->count() > 0) {
                    continue;
                }
                //2. else create new contact
                $avatar = null;
                if ($request->hasFile("contacts.$key.avatar")) {
                    $avatar = Contact::uploadOrigin($request->file("contacts.$key.avatar"), Contact::$subFolder);
                }
                $contactUser = User::where('phone_number', $phone)->first();
                $data = [
                    'user_id' => $user->id,
                    'avatar' => $avatar ?? null,
                    'name' => $c['name'] ?? null,
                    'phone_number' => $c['phone_number'] ?? null,
                    'dial_code' => $c['dial_code'] ?? null,
                ];
                if ($contactUser) {
                    $data = [
                        'user_contact_id' => $contactUser->id, //link contact user
                        'user_id' => $user->id,
                        'avatar' => $avatar ?? null,
                        'name' => $c['name'] ?? null,
                    ];
                }
                Contact::create($data);
            }
        });

        return response()->json(['message' => trans('contact.import_success')]);
    }

    /**
     * Add contact - link with user
     *
     *
     * @return ContactResource|\Illuminate\Http\JsonResponse
     */
    public function linkContact($userContactId, Request $request)
    {
        $user = \Auth::user()->user;
        $userContact = User::where('id', $userContactId)->first();
        $existContact = Contact::where('user_id', $user->id)->where('user_contact_id', $userContactId)->count();

        if (! $userContact || $userContact->id == $user->id) {
            return response()->json(['message' => __('contact.input_invalid')], 422);
        }
        if ($existContact > 0) {
            return response()->json(['message' => __('contact.error_contact_exists')], 500);
        }
        $contact = Contact::create([
            'user_id' => $user->id,
            'user_contact_id' => $userContactId,
        ]);
        ContactResource::withoutWrapping();

        return new ContactResource($contact);
    }

    /**
     * Delete contact
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $user = \Auth::user()->user;
        $contact = Contact::where('id', $id)->where('user_id', $user->id)->first();
        if (! $contact) {
            return response()->json(['message' => __('contact.error_contact_not_exists')], 500);
        }
        $contact->delete();

        return response()->json(['message' => __('contact.contact_deleted')]);
    }

    /**
     * Sửa liên hệ:
     * - Đối với trường hợp liên hệ ko có liên kết với user nào thì lưu như bình thường.
     * - Ngược lại:
     * + nếu thông tin liên hệ khác với thông tin user đang liên kết thì sẽ cập nhật thông tin vào bảng contacts
     * + Với trường hợp số điện thoại liên hệ khác với user được liên kết thì remove user liên kết khỏi liên hệ
     *
     *
     * @return ContactResource|\Illuminate\Http\JsonResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update($id, Request $request)
    {
        $user = \Auth::user()->user;

        $this->validate($request, [
            'phone_number' => ['required', 'string', 'max:20', Rule::unique('contacts')->where(function ($query) use ($user) {
                return $query->where('user_id', $user->id);
            })->ignore($id)],
            'dial_code' => ['required', Rule::in(Country::getCountryDialCodes())],
            'name' => ['required', 'string', 'max:50'],
            'avatar' => ['nullable', 'image', 'mimes:jpeg,bmp,png,jpg,gif', 'max:'.Contact::AVATAR_MAXSIZE],
        ]);

        $contact = Contact::where('id', $id)->where('user_id', $user->id)->first();
        if (! $contact) {
            return response()->json(['message' => __('contact.error_contact_not_exists')], 500);
        }

        \DB::transaction(function () use ($request, &$contact) {
            $data = $request->all();
            if ($request->hasFile('avatar')) {
                Contact::deleteImage($contact->avatar);
                $data['avatar'] = Contact::uploadOrigin($request->file('avatar'), Contact::$subFolder);
            }
            if (! empty($contact->user_contact_id)) {
                //Chỉ lưu lại thông tin nếu nó thay đổi so với dữ liệu gốc của user
                $contactUser = User::where('id', $contact->user_contact_id)->first();
                if ($contactUser) {
                    //chỉ xử lý với trường contact bằng rỗng, thì check xem thông tin đó ở user có thay đổi ko để update
                    if (empty($contact->name)) {
                        $name = $data['name'];
                        unset($data['name']);
                        if ($contactUser->name != $name) {
                            $data['name'] = $name;
                        }
                    }
                    if (empty($contact->phone_number)) {
                        $phoneNumber = $data['phone_number'];
                        $dialCode = $data['dial_code'];
                        unset($data['phone_number']);
                        unset($data['dial_code']);
                        if ($contactUser->phone_number != $phoneNumber) {
                            $data['phone_number'] = $phoneNumber;
                            $data['dial_code'] = $dialCode;
                        }
                    }
                }
            }
            //link contact user
            $data['user_contact_id'] = null;
            $contactUserOther = User::where('phone_number', $request->get('phone_number'))->where('dial_code', $request->get('dial_code'))->first();
            if ($contactUserOther) {
                $data['user_contact_id'] = $contactUserOther->id;
            }
            $contact->update($data);

        });
        ContactResource::withoutWrapping();

        return new ContactResource($contact);
    }

    public function addConnect($id)
    {
        return response()->json($this->connectycube($id));
    }
}
