<?php
namespace App\Langs;

use WebFiori\Framework\Lang;

/**
 * Arabic language variables for the blog.
 */
class LangAR extends Lang {
    public function __construct() {
        parent::__construct('rtl', 'AR');

        $this->createAndSet('nav', [
            'home' => 'الرئيسية',
            'login' => 'تسجيل الدخول',
            'logout' => 'تسجيل الخروج',
            'admin' => 'لوحة التحكم',
            'categories' => 'التصنيفات',
        ]);
        $this->createAndSet('blog', [
            'title' => 'مدونة WebFiori',
            'no-posts' => 'لا توجد مقالات.',
            'read-more' => 'اقرأ المزيد',
            'by' => 'بواسطة',
            'in' => 'في',
            'comments' => 'التعليقات',
            'leave-comment' => 'اترك تعليقاً',
            'name' => 'الاسم',
            'email' => 'البريد الإلكتروني',
            'comment' => 'التعليق',
            'submit' => 'إرسال',
            'published' => 'منشور',
            'draft' => 'مسودة',
            'page' => 'صفحة',
            'of' => 'من',
        ]);
        $this->createAndSet('admin', [
            'dashboard' => 'لوحة التحكم',
            'manage-posts' => 'إدارة المقالات',
            'manage-categories' => 'إدارة التصنيفات',
            'create-post' => 'إنشاء مقال',
            'edit-post' => 'تعديل مقال',
            'title' => 'العنوان',
            'slug' => 'الرابط',
            'content' => 'المحتوى',
            'category' => 'التصنيف',
            'status' => 'الحالة',
            'actions' => 'الإجراءات',
            'save' => 'حفظ',
            'delete' => 'حذف',
            'edit' => 'تعديل',
        ]);
        $this->createAndSet('auth', [
            'login-title' => 'تسجيل دخول المدير',
            'email' => 'البريد الإلكتروني',
            'password' => 'كلمة المرور',
            'login-btn' => 'دخول',
            'invalid-credentials' => 'بريد إلكتروني أو كلمة مرور غير صحيحة.',
        ]);
    }
}
