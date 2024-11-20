{{--we send translations as property because we reuse this component on admin side where $t does not required. to awoid annecessary $t including on admin side--}}
<avatar-uploader :profile-image-url="'{{ $client->avatar_url }}'"
                 :client-id="{{$client->id}}"
                 :trans="{{Js::from([
                    'common.are_you_sure_you_want_to_remove_profile_picture' => __('common.are_you_sure_you_want_to_remove_profile_picture'),
                    'common.yes' => __('common.yes'),
                    'common.no' => __('common.no'),
                    'common.image' => __('common.image'),
                    'common.delete' => __('common.delete'),
                ])}}"
                 default-avatar-url="/images/icons/Account.svg"
></avatar-uploader>
