{{-- Class strings of the old design's chat widget (navy shadows, blue gradient). Kept in a file of their own so the G stylesheets, which scan chat-widget.blade.php, do not generate them. --}}
{!! json_encode([
    'panel' => 'shadow-[0_30px_80px_-20px_rgb(15_26_43/0.55)] sm:rounded-3xl',
    'icon' => 'bg-brand-gradient',
    'teaser' => 'shadow-[0_20px_50px_-15px_rgb(15_26_43/0.45)]',
    'toggle' => 'bg-gradient-to-br from-brand-700 to-brand-500 shadow-[0_18px_45px_-10px_rgb(45_99_163/0.8)] hover:shadow-[0_22px_50px_-10px_rgb(45_99_163/0.95)]',
]) !!}
