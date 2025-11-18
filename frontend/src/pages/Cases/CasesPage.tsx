import { Link } from 'react-router-dom';
import { useForm } from 'react-hook-form';
import { CaseApi } from '../../api/modules/cases';
import { WorkflowApi } from '../../api/modules/workflow';
import { ClientApi } from '../../api/modules/clients';
import { useAsyncData } from '../../hooks/useAsyncData';

export const CasesPage = () => {
  const { data: cases, refetch } = useAsyncData(['cases'], CaseApi.list);
  const { data: templates } = useAsyncData(['workflow-templates'], WorkflowApi.templates);
  const { data: clients } = useAsyncData(['clients'], ClientApi.list);
  const { register, handleSubmit, reset } = useForm();

  const onSubmit = handleSubmit(async (values) => {
    await CaseApi.create(values as any);
    reset();
    refetch();
  });

  return (
    <section className="space-y-6">
      <header>
        <h2 className="text-2xl font-semibold">Dosya Yönetimi</h2>
        <p className="text-slate-600">Dava, icra, arabuluculuk dosyaları burada listelenir.</p>
      </header>

      <form
        onSubmit={onSubmit}
        className="grid grid-cols-5 gap-4 rounded-xl border border-[#E2E8F0] bg-white p-4 shadow-sm"
      >
        <input className="input" placeholder="Dosya No" {...register('case_no', { required: true })} />
        <input className="input" placeholder="Başlık" {...register('title', { required: true })} />
        <select className="input" {...register('client_id', { required: true })}>
          <option value="">Müvekkil seçin</option>
          {clients?.map((client: any) => (
            <option key={client.id} value={client.id}>
              {client.name}
            </option>
          ))}
        </select>
        <select className="input" {...register('type')}>
          <option value="lawsuit">Dava</option>
          <option value="enforcement">İcra</option>
          <option value="mediation">Arabuluculuk</option>
        </select>
        <select className="input" {...register('workflow_template_id')}>
          <option value="">Workflow şablonu</option>
          {templates?.map((tpl: any) => (
            <option key={tpl.id} value={tpl.id}>
              {tpl.name}
            </option>
          ))}
        </select>
        <button
          className="col-span-5 rounded-lg bg-[#2463eb] px-4 py-2 text-white font-semibold hover:bg-[#1d4fd8]"
          type="submit"
        >
          Dosya oluştur
        </button>
      </form>

      <div className="grid gap-4">
        {cases?.map((item: any) => (
          <article key={item.id} className="rounded-xl border border-[#E2E8F0] bg-white p-4 shadow-sm">
            <div className="flex items-center justify-between">
              <div>
                <h3 className="text-lg font-semibold">{item.title}</h3>
                <p className="text-sm text-[#4A5568]">{item.case_no}</p>
                <p className="text-sm text-[#4A5568]">Tür: {item.type}</p>
              </div>
              <Link
                to={`/cases/${item.id}`}
                className="flex items-center gap-2 rounded-lg border border-[#E2E8F0] px-4 py-2 text-sm font-semibold text-[#2463eb] hover:bg-[#2463eb]/10"
              >
                <span className="material-symbols-outlined text-base">open_in_new</span>
                Detay
              </Link>
            </div>
          </article>
        ))}
      </div>
    </section>
  );
};
