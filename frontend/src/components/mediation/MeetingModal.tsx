import { ChangeEvent, FormEvent, useEffect, useState } from 'react';
import { MediationMeeting } from '../../types/mediation';

interface Props {
  open: boolean;
  meeting?: MediationMeeting | null;
  onClose: () => void;
  onSave: (meeting: Omit<MediationMeeting, 'id'>) => void;
}

const defaultValues = {
  date: '',
  time: '',
  location: '',
  attendees: '',
  result: '',
  notes: '',
};

export const MeetingModal = ({ open, meeting, onClose, onSave }: Props) => {
  const [formState, setFormState] = useState(defaultValues);

  useEffect(() => {
    if (meeting) {
      setFormState({
        date: meeting.date,
        time: meeting.time,
        location: meeting.location,
        attendees: meeting.attendees.join(', '),
        result: meeting.result,
        notes: meeting.notes ?? '',
      });
    } else {
      setFormState(defaultValues);
    }
  }, [meeting, open]);

  if (!open) {
    return null;
  }

  const handleChange = (key: keyof typeof formState) => (event: ChangeEvent<HTMLInputElement | HTMLTextAreaElement>) => {
    setFormState((prev) => ({ ...prev, [key]: event.target.value }));
  };

  const handleSubmit = (event: FormEvent) => {
    event.preventDefault();
    onSave({
      date: formState.date,
      time: formState.time,
      location: formState.location,
      result: formState.result,
      attendees: formState.attendees
        .split(',')
        .map((item) => item.trim())
        .filter(Boolean),
      notes: formState.notes,
    });
  };

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4">
      <div className="w-full max-w-2xl rounded-3xl bg-white p-6 shadow-lg">
        <div className="flex items-center justify-between">
          <div>
            <p className="text-sm uppercase tracking-wide text-slate-400">Toplantı</p>
            <h2 className="text-2xl font-semibold text-slate-900">{meeting ? 'Toplantıyı Düzenle' : 'Yeni Toplantı'}</h2>
          </div>
          <button onClick={onClose} className="rounded-full bg-slate-100 p-2 text-slate-500 hover:bg-slate-200">
            <span className="material-symbols-outlined">close</span>
          </button>
        </div>

        <form onSubmit={handleSubmit} className="mt-6 space-y-4">
          <div className="grid gap-4 md:grid-cols-2">
            <label className="flex flex-col gap-1 text-sm text-slate-600">
              Tarih
              <input
                required
                type="date"
                value={formState.date}
                onChange={handleChange('date')}
                className="rounded-2xl border border-slate-200 px-4 py-2 text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#2463eb]"
              />
            </label>
            <label className="flex flex-col gap-1 text-sm text-slate-600">
              Saat
              <input
                required
                type="time"
                value={formState.time}
                onChange={handleChange('time')}
                className="rounded-2xl border border-slate-200 px-4 py-2 text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#2463eb]"
              />
            </label>
          </div>
          <label className="flex flex-col gap-1 text-sm text-slate-600">
            Yer
            <input
              required
              value={formState.location}
              onChange={handleChange('location')}
              className="rounded-2xl border border-slate-200 px-4 py-2 text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#2463eb]"
            />
          </label>
          <label className="flex flex-col gap-1 text-sm text-slate-600">
            Katılanlar (virgülle ayırın)
            <input
              required
              value={formState.attendees}
              onChange={handleChange('attendees')}
              className="rounded-2xl border border-slate-200 px-4 py-2 text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#2463eb]"
            />
          </label>
          <label className="flex flex-col gap-1 text-sm text-slate-600">
            Sonuç
            <input
              required
              value={formState.result}
              onChange={handleChange('result')}
              className="rounded-2xl border border-slate-200 px-4 py-2 text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#2463eb]"
            />
          </label>
          <label className="flex flex-col gap-1 text-sm text-slate-600">
            Notlar
            <textarea
              rows={3}
              value={formState.notes}
              onChange={handleChange('notes')}
              className="rounded-2xl border border-slate-200 px-4 py-2 text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#2463eb]"
            />
          </label>
          <div className="flex justify-end gap-3 pt-2">
            <button
              type="button"
              onClick={onClose}
              className="rounded-2xl border border-slate-200 px-4 py-2 font-semibold text-slate-600 hover:bg-slate-50"
            >
              İptal
            </button>
            <button
              type="submit"
              className="rounded-2xl bg-[#2463eb] px-6 py-2 font-semibold text-white hover:bg-[#1d4fd8]"
            >
              Kaydet
            </button>
          </div>
        </form>
      </div>
    </div>
  );
};
