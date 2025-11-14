import { MediationNote } from '../../types/mediation';

interface Props {
  note: MediationNote;
}

export const NoteCard = ({ note }: Props) => (
  <article className="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
    <div className="flex items-center justify-between">
      <div>
        <p className="text-sm font-semibold text-slate-900">{note.author}</p>
        <p className="text-xs text-slate-500">
          {new Date(note.createdAt).toLocaleString('tr-TR', {
            day: '2-digit',
            month: 'short',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
          })}
        </p>
      </div>
      <span className="material-symbols-outlined text-slate-300 text-lg">lock</span>
    </div>
    <p className="mt-3 text-sm leading-relaxed text-slate-700">{note.content}</p>
  </article>
);
