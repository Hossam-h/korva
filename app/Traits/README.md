# HasFileAttachment Trait

Trait قابل لإعادة الاستخدام للتعامل مع رفع وحذف الملفات في Laravel.

## المميزات

- ✅ رفع وتحديث الملفات تلقائياً
- ✅ حذف الملفات القديمة تلقائياً عند التحديث
- ✅ حذف الملفات تلقائياً عند حذف الـ Model
- ✅ دعم جميع Storage Drivers (local, S3, etc.)
- ✅ مسارات مخصصة لكل Model
- ✅ آمن ومقاوم للأخطاء

## التثبيت

1. استخدم الـ Trait في Model الخاص بك:

```php
use App\Traits\HasFileAttachment;

class YourModel extends Model
{
    use HasFileAttachment;
    
    protected $fillable = [
        'image',
        'thumbnail',
        // ... other fields
    ];
    
    // اختياري: تحديد حقول الملفات يدوياً
    protected function getFileFields(): array
    {
        return ['image', 'thumbnail'];
    }
}
```

## الاستخدام

### رفع ملف جديد أو تحديث ملف موجود

```php
$model = YourModel::find(1);

// رفع ملف جديد (سيحذف القديم تلقائياً إذا كان موجوداً)
$model->uploadFile(
    $request->file('image'),  // UploadedFile
    'image',                   // اسم الحقل في قاعدة البيانات
    'custom/folder',           // اختياري: مجلد مخصص
    's3'                      // اختياري: disk مخصص (default: config filesystems.default)
);

// النتيجة: true عند النجاح، false عند الفشل
```

### حذف ملف

```php
$model = YourModel::find(1);

// حذف ملف محدد
$model->deleteFile('image');

// أو مع تحديد disk
$model->deleteFile('image', 's3');
```

### الحصول على URL الملف

```php
$model = YourModel::find(1);

// الحصول على URL الملف
$url = $model->getFileUrl('image');
// النتيجة: https://example.com/storage/path/to/file.jpg

// أو مع disk مخصص
$url = $model->getFileUrl('image', 's3');
```

### الحصول على مسار الملف

```php
$model = YourModel::find(1);

$path = $model->getFilePath('image');
// النتيجة: /full/path/to/storage/file.jpg
```

### التحقق من وجود ملف

```php
$model = YourModel::find(1);

if ($model->hasFile('image')) {
    // الملف موجود
}
```

## مثال كامل في Controller

```php
use App\Models\PerformanceTrial;
use Illuminate\Http\Request;

class PerformanceTrialController extends Controller
{
    public function store(Request $request)
    {
        $trial = PerformanceTrial::create($request->except('thumbnail'));
        
        if ($request->hasFile('thumbnail')) {
            $trial->uploadFile(
                $request->file('thumbnail'),
                'thumbnail',
                'performance_trials'  // مجلد مخصص
            );
        }
        
        return response()->json($trial);
    }
    
    public function update(Request $request, $id)
    {
        $trial = PerformanceTrial::findOrFail($id);
        $trial->update($request->except('thumbnail'));
        
        if ($request->hasFile('thumbnail')) {
            // سيحذف الملف القديم تلقائياً
            $trial->uploadFile(
                $request->file('thumbnail'),
                'thumbnail',
                'performance_trials'
            );
        }
        
        return response()->json($trial);
    }
    
    public function destroy($id)
    {
        $trial = PerformanceTrial::findOrFail($id);
        
        // سيحذف الملف تلقائياً عند حذف الـ Model
        $trial->delete();
        
        return response()->json(['message' => 'Deleted successfully']);
    }
}
```

## الحذف التلقائي

عند حذف Model، سيتم حذف جميع الملفات المرتبطة به تلقائياً:

```php
$model = YourModel::find(1);
$model->delete(); // سيحذف جميع الملفات تلقائياً
```

## تخصيص مجلد الملفات

```php
// استخدام مجلد افتراضي (اسم Model بصيغة الجمع)
$model->uploadFile($file, 'image');
// سيحفظ في: models/images/filename.jpg

// استخدام مجلد مخصص
$model->uploadFile($file, 'image', 'custom/path');
// سيحفظ في: custom/path/filename.jpg
```

## تخصيص Storage Disk

```php
// استخدام disk افتراضي
$model->uploadFile($file, 'image');

// استخدام S3
$model->uploadFile($file, 'image', null, 's3');

// استخدام disk مخصص
$model->uploadFile($file, 'image', 'folder', 'custom_disk');
```

## تحديد حقول الملفات يدوياً

إذا كنت تريد تحديد حقول الملفات يدوياً:

```php
class YourModel extends Model
{
    use HasFileAttachment;
    
    protected function getFileFields(): array
    {
        return ['thumbnail', 'image', 'logo'];
    }
}
```

## معالجة الأخطاء

جميع الدوال ترجع `true` عند النجاح و `false` عند الفشل. الأخطاء يتم تسجيلها في Laravel Log:

```php
if (!$model->uploadFile($file, 'image')) {
    // حدث خطأ، راجع Laravel Log
}
```

## ملاحظات

- الملفات القديمة يتم حذفها تلقائياً عند رفع ملف جديد
- عند حذف Model، يتم حذف جميع الملفات المرتبطة به تلقائياً
- الـ Trait يعمل مع جميع Storage Drivers المدعومة في Laravel
- أسماء الملفات يتم توليدها بشكل فريد لتجنب التعارض
