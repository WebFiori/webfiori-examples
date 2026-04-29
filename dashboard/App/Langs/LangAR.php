<?php
namespace App\Langs;

use WebFiori\Framework\Lang;

class LangAR extends Lang {
    public function __construct() {
        parent::__construct('rtl', 'AR');
        $this->createAndSet('nav', [
            'dashboard' => 'لوحة التحكم', 'projects' => 'المشاريع', 'reports' => 'التقارير',
            'users' => 'المستخدمون', 'audit-log' => 'سجل المراجعة', 'settings' => 'الإعدادات',
            'login' => 'تسجيل الدخول', 'logout' => 'تسجيل الخروج',
        ]);
        $this->createAndSet('common', [
            'name' => 'الاسم', 'email' => 'البريد', 'role' => 'الدور', 'status' => 'الحالة',
            'actions' => 'الإجراءات', 'save' => 'حفظ', 'delete' => 'حذف', 'edit' => 'تعديل',
            'create' => 'إنشاء', 'submit' => 'إرسال', 'search' => 'بحث',
            'password' => 'كلمة المرور', 'login-title' => 'تسجيل دخول لوحة التحكم',
            'welcome' => 'مرحباً', 'active' => 'نشط', 'id' => 'المعرف',
            'description' => 'الوصف', 'owner' => 'المالك', 'created' => 'تاريخ الإنشاء',
            'title' => 'العنوان', 'generated-by' => 'أنشئ بواسطة',
            'user' => 'المستخدم', 'action' => 'الإجراء', 'entity' => 'الكيان',
            'ip' => 'عنوان IP', 'date' => 'التاريخ', 'yes' => 'نعم', 'no' => 'لا',
            'theme' => 'المظهر', 'language' => 'اللغة', 'light' => 'فاتح', 'dark' => 'داكن',
            'current-theme' => 'المظهر الحالي', 'not-found' => 'غير موجود',
            'manage-users' => 'إدارة المستخدمين', 'project-detail' => 'تفاصيل المشروع',
        ]);
    }
}
