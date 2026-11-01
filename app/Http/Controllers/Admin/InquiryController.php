<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Inquiry;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InquiryController extends Controller
{
    public function index(Request $request)
    {
        $inquiries = $this->filtered($request)->latest()->paginate(20)->withQueryString();

        $counts = Inquiry::selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');
        $counts['all'] = Inquiry::where('status', '!=', 'spam')->count();
        $counts['unread'] = Inquiry::unread()->where('status', '!=', 'spam')->count();

        $lines = Inquiry::query()->distinct()->orderBy('product_line')->pluck('product_line');

        return view('admin.inquiries.index', compact('inquiries', 'counts', 'lines'));
    }

    public function show(Inquiry $inquiry)
    {
        if ($inquiry->is_unread) {
            $inquiry->forceFill(['read_at' => now()])->save();
        }

        $inquiry->load('category');

        $previous = Inquiry::where('email', $inquiry->email)->where('id', '!=', $inquiry->id)->latest()->limit(5)->get();
        $prevId = Inquiry::where('id', '>', $inquiry->id)->orderBy('id')->value('id');   // newer
        $nextId = Inquiry::where('id', '<', $inquiry->id)->orderByDesc('id')->value('id'); // older

        return view('admin.inquiries.show', compact('inquiry', 'previous', 'prevId', 'nextId'));
    }

    public function update(Request $request, Inquiry $inquiry)
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(array_keys(Inquiry::STATUSES))],
            'admin_notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $inquiry->update($data);

        return back()->with('success', 'Inquiry updated.');
    }

    public function markUnread(Inquiry $inquiry)
    {
        $inquiry->forceFill(['read_at' => null])->save();

        return redirect()->route('admin.inquiries.index')->with('success', 'Marked as unread.');
    }

    public function destroy(Inquiry $inquiry)
    {
        $inquiry->delete();

        return redirect()->route('admin.inquiries.index')->with('success', 'Inquiry deleted.');
    }

    public function bulk(Request $request)
    {
        $data = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer'],
            'action' => ['required', Rule::in(['read', 'unread', 'delete', ...array_keys(Inquiry::STATUSES)])],
        ]);

        $query = Inquiry::whereIn('id', $data['ids']);

        match ($data['action']) {
            'read' => $query->update(['read_at' => now()]),
            'unread' => $query->update(['read_at' => null]),
            'delete' => $query->delete(),
            default => $query->update(['status' => $data['action']]),
        };

        return back()->with('success', count($data['ids']).' inquiries updated.');
    }

    public function export(Request $request): StreamedResponse
    {
        $rows = $this->filtered($request)->latest()->get();
        $name = 'inquiries-'.now()->format('Y-m-d').'.csv';

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // UTF-8 BOM so Excel shows accents correctly
            fputcsv($out, ['ID', 'Date', 'Name', 'Club / Gym / Brand', 'Email', 'WhatsApp', 'Product line',
                'Quantity', 'Destination country', 'Customisation', 'Message', 'Status', 'Notes']);
            foreach ($rows as $r) {
                fputcsv($out, [$r->id, $r->created_at->format('Y-m-d H:i'), $r->name, $r->company, $r->email,
                    $r->whatsapp, $r->product_line, $r->quantity, $r->destination_country, $r->customisation,
                    $r->message, $r->status_label, $r->admin_notes]);
            }
            fclose($out);
        }, $name, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function filtered(Request $request): Builder
    {
        $status = $request->input('status', 'all');

        return Inquiry::query()
            ->when($status === 'all', fn ($q) => $q->where('status', '!=', 'spam'))
            ->when($status === 'unread', fn ($q) => $q->whereNull('read_at')->where('status', '!=', 'spam'))
            ->when(array_key_exists($status, Inquiry::STATUSES), fn ($q) => $q->where('status', $status))
            ->when($request->line, fn ($q, $l) => $q->where('product_line', $l))
            ->when($request->q, fn ($q, $s) => $q->where(fn ($w) => $w
                ->where('name', 'like', "%{$s}%")
                ->orWhere('email', 'like', "%{$s}%")
                ->orWhere('company', 'like', "%{$s}%")
                ->orWhere('destination_country', 'like', "%{$s}%")
                ->orWhere('whatsapp', 'like', "%{$s}%")))
            ->when($request->from, fn ($q, $d) => $q->whereDate('created_at', '>=', $d))
            ->when($request->to, fn ($q, $d) => $q->whereDate('created_at', '<=', $d));
    }
}
