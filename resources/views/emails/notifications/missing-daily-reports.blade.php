@component('mail::message')
    # Hello {{ $notifiable->name }},

    We’ve reviewed the daily reports submitted by your team for this month (up to today), and found that
    **{{ count($employees) }} employee(s)** have missing entries.

    ---

    ## 📋 Missing Report Summary

    @component('mail::table')
        | # | Employee Name | NIK | Missing Dates |
        |--:|---------------|-------|----------------|
        @foreach ($employees as $index => $entry)
            | {{ $index + 1 }} | {{ $entry['employee']->Nama }} | {{ $entry['employee']->NIK }} |
            {!! implode('<br>', $entry['dates']) !!} |
        @endforeach
    @endcomponent

    Please follow up with the employees listed above to ensure timely completion of their daily reports.

    Thanks,
    **Daily Report System**
@endcomponent
