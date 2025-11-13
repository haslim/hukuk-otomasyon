import { useForm } from 'react-hook-form';
import { CaseApi } from '../../api/modules/cases';
import { WorkflowApi } from '../../api/modules/workflow';
import { useAsyncData } from '../../hooks/useAsyncData';

export const CasesPage = () => {
  const { data: cases, refetch } = useAsyncData(['cases'], CaseApi.list);
  const { data: templates } = useAsyncData(['workflow-templates'], WorkflowApi.templates);
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

      <form onSubmit={onSubmit} className="grid grid-cols-5 gap-4 rounded bg-white p-4 shadow">
        <input className="input" placeholder="Dosya No" {...register('case_no', { required: true })} />
        <input className="input" placeholder="Başlık" {...register('title', { required: true })} />
        <input className="input" placeholder="Müvekkil ID" {...register('client_id', { required: true })} />
        <select className="input" {...register('type')}>
          <option value="lawsuit">Dava</option>
          <option value="enforcement">İcra</option>
          <option value="mediation">Arabuluculuk</option>
        </select>
        <select className="input" {...register('workflow_template_id')}>
          <option value="">Workflow Şablonu</option>
          {templates?.map((tpl: any) => (
            <option key={tpl.id} value={tpl.id}>
              {tpl.name}
            </option>
          ))}
        </select>
        <button className="col-span-5 rounded bg-indigo-600 px-4 py-2 text-white" type="submit">
          Dosya Oluştur
        </button>
      </form>

      <div className="grid gap-4">
        {cases?.map((item: any) => (
          <article key={item.id} className="rounded bg-white p-4 shadow">
            <h3 className="text-lg font-semibold">{item.title}</h3>
            <p className="text-sm text-slate-500">{item.case_no}</p>
            <p className="text-sm">Tür: {item.type}</p>
          </article>
        ))}
      </div>
    </section>
  );
};
