<?php

namespace MotionArray\Models\StaticData;

class AccessServices extends StaticDBData
{
    public const REVIEW_AFTER_EFFECTS = 'After Effects';
    public const REVIEW_AFTER_EFFECTS_ID = 1;

    public const REVIEW_PREMIERE_PRO = 'Premiere Pro';
    public const REVIEW_PREMIERE_PRO_ID = 2;

    public const REVIEW_AUDIO = 'Audio';
    public const REVIEW_AUDIO_ID = 3;

    public const REVIEW_VIDEO = 'Video';
    public const REVIEW_VIDEO_ID = 4;

    public const APPROVED_AFTER_EFFECTS = 'After Effects';
    public const APPROVED_AFTER_EFFECTS_ID = 5;

    public const APPROVED_PREMIERE_PRO = 'Premiere Pro';
    public const APPROVED_PREMIERE_PRO_ID = 6;

    public const APPROVED_AUDIO = 'Audio';
    public const APPROVED_AUDIO_ID = 7;

    public const APPROVED_VIDEO = 'Video';
    public const APPROVED_VIDEO_ID = 8;

    public const DOWNLOAD_HISTORY = 'Download History';
    public const DOWNLOAD_HISTORY_ID = 9;

    public const USER_CREDITS = 'Credits';
    public const USER_CREDITS_ID = 10;

    public const USER_PASSWORD = 'Password';
    public const USER_PASSWORD_ID = 11;

    public const USER_LOGIN_AS_USER = 'Login as User';
    public const USER_LOGIN_AS_USER_ID = 12;

    public const USER_DELETE_FREE_USER = 'Delete Free User';
    public const USER_DELETE_FREE_USER_ID = 16;

    protected $modelClass = \MotionArray\Models\AccessService::class;

    protected $data = [
        [
            'id' => self::REVIEW_AFTER_EFFECTS_ID,
            'name' => self::REVIEW_AFTER_EFFECTS,
            'access_service_category_id' => AccessServiceCategories::REVIEW_PRODUCTS_ID,
        ],
        [
            'id' => self::REVIEW_PREMIERE_PRO_ID,
            'name' => self::REVIEW_PREMIERE_PRO,
            'access_service_category_id' => AccessServiceCategories::REVIEW_PRODUCTS_ID,
        ],
        [
            'id' => self::REVIEW_AUDIO_ID,
            'name' => self::REVIEW_AUDIO,
            'access_service_category_id' => AccessServiceCategories::REVIEW_PRODUCTS_ID,
        ],
        [
            'id' => self::REVIEW_VIDEO_ID,
            'name' => self::REVIEW_VIDEO,
            'access_service_category_id' => AccessServiceCategories::REVIEW_PRODUCTS_ID,
        ],
        [
            'id' => self::APPROVED_AFTER_EFFECTS_ID,
            'name' => self::APPROVED_AFTER_EFFECTS,
            'access_service_category_id' => AccessServiceCategories::APPROVED_PRODUCTS_ID,
        ],
        [
            'id' => self::APPROVED_PREMIERE_PRO_ID,
            'name' => self::APPROVED_PREMIERE_PRO,
            'access_service_category_id' => AccessServiceCategories::APPROVED_PRODUCTS_ID,
        ],
        [
            'id' => self::APPROVED_AUDIO_ID,
            'name' => self::APPROVED_AUDIO,
            'access_service_category_id' => AccessServiceCategories::APPROVED_PRODUCTS_ID,
        ],
        [
            'id' => self::APPROVED_VIDEO_ID,
            'name' => self::APPROVED_VIDEO,
            'access_service_category_id' => AccessServiceCategories::APPROVED_PRODUCTS_ID,
        ],
        [
            'id' => self::DOWNLOAD_HISTORY_ID,
            'name' => self::DOWNLOAD_HISTORY,
            'access_service_category_id' => AccessServiceCategories::USERS_ID,
        ],
        [
            'id' => self::USER_CREDITS_ID,
            'name' => self::USER_CREDITS,
            'access_service_category_id' => AccessServiceCategories::USERS_ID,
        ],
        [
            'id' => self::USER_PASSWORD_ID,
            'name' => self::USER_PASSWORD,
            'access_service_category_id' => AccessServiceCategories::USERS_ID,
        ],
        [
            'id' => self::USER_LOGIN_AS_USER_ID,
            'name' => self::USER_LOGIN_AS_USER,
            'access_service_category_id' => AccessServiceCategories::USERS_ID,
        ],
        [
            'id' => self::USER_DELETE_FREE_USER_ID,
            'name' => self::USER_DELETE_FREE_USER,
            'access_service_category_id' => AccessServiceCategories::USERS_ID,
        ],
    ];
}
