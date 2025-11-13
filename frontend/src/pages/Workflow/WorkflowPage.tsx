import { WorkflowApi } from '../../api/modules/workflow';
import { useAsyncData } from '../../hooks/useAsyncData';

export const WorkflowPage = () => {
  const { data, isLoading } = useAsyncData(['workflow-templates'], WorkflowApi.templates);

  if (isLoading) return <p>Şablonlar yükleniyor...</p>;

  return (
    <section className="space-y-4">
      <header>
        <h2 className="text-2xl font-semibold">Workflow Şablonları</h2>
        <p className="text-slate-600">Her iş tipi için adımların durumunu takip edin.</p>
      </header>
      <div className="space-y-4">
        {data?.map((template: any) => (
          <article key={template.id} className="rounded bg-white p-4 shadow">
            <h3 className="text-lg font-semibold">{template.name}</h3>
            <ul className="mt-2 list-disc pl-4 text-sm text-slate-600">
              {template.steps?.map((step: any) => (
                <li key={step.id}>
                  {step.title} {step.is_required ? '(zorunlu)' : '(opsiyonel)'}
                </li>
              ))}
            </ul>
          </article>
        ))}
      </div>
    </section>
  );
};
