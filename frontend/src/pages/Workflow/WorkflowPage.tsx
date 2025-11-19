import { FormEvent, useState } from 'react';
import { WorkflowApi, WorkflowStepPayload } from '../../api/modules/workflow';
import { useAsyncData } from '../../hooks/useAsyncData';
import { useNotification } from '../../context/NotificationContext';

interface WorkflowDraftStep {
  id: string;
  title: string;
  isRequired: boolean;
}

const buildStepId = () =>
  typeof globalThis.crypto !== 'undefined' && 'randomUUID' in globalThis.crypto
    ? globalThis.crypto.randomUUID()
    : `${Date.now()}-${Math.random().toString(36).slice(2)}`;

const createDraftStep = (): WorkflowDraftStep => ({
  id: buildStepId(),
  title: '',
  isRequired: true,
});

export const WorkflowPage = () => {
  const { data, isLoading, refetch } = useAsyncData(['workflow-templates'], WorkflowApi.templates);
  const templates = data ?? [];
  const { notify } = useNotification();
  const [name, setName] = useState('');
  const [caseType, setCaseType] = useState('');
  const [steps, setSteps] = useState<WorkflowDraftStep[]>(() => [createDraftStep()]);
  const [isSaving, setIsSaving] = useState(false);

  const addStep = () => {
    setSteps((prev) => [...prev, createDraftStep()]);
  };

  const removeStep = (id: string) => {
    setSteps((prev) => {
      if (prev.length === 1) {
        return prev;
      }
      return prev.filter((step) => step.id !== id);
    });
  };

  const updateStepTitle = (id: string, value: string) => {
    setSteps((prev) => prev.map((step) => (step.id === id ? { ...step, title: value } : step)));
  };

  const updateStepRequired = (id: string, value: boolean) => {
    setSteps((prev) => prev.map((step) => (step.id === id ? { ...step, isRequired: value } : step)));
  };

  const handleSubmit = async (event: FormEvent<HTMLFormElement>) => {
    event.preventDefault();
    if (isSaving) return;

    const trimmedName = name.trim();
    const trimmedCaseType = caseType.trim();

    if (!trimmedName) {
      notify('Template name is required', 'error');
      return;
    }

    if (!trimmedCaseType) {
      notify('Case type is required', 'error');
      return;
    }

    const sanitizedSteps: WorkflowStepPayload[] = steps
      .map((step) => ({ title: step.title.trim(), is_required: step.isRequired }))
      .filter((step) => step.title !== '');

    if (!sanitizedSteps.length) {
      notify('Add at least one workflow step', 'error');
      return;
    }

    setIsSaving(true);
    try {
      await WorkflowApi.createTemplate({
        name: trimmedName,
        case_type: trimmedCaseType,
        steps: sanitizedSteps,
      });
      notify('Workflow template created', 'success');
      setName('');
      setCaseType('');
      setSteps([createDraftStep()]);
      refetch();
    } catch (error) {
      console.error(error);
      notify('Unable to create workflow template', 'error');
    } finally {
      setIsSaving(false);
    }
  };

  return (
    <section className="space-y-6 max-w-6xl">
      <header className="space-y-2">
        <h2 className="text-2xl font-semibold text-[#0f172a]">Workflow templates</h2>
        <p className="text-sm text-[#64748b]">
          Create reusable step sequences for different case types and reuse them when opening a case.
        </p>
      </header>

      <div className="grid gap-6 lg:grid-cols-[minmax(0,360px)_1fr]">
        <div className="rounded-2xl border border-[#E2E8F0] bg-white p-5 shadow-sm">
          <div className="flex items-center justify-between">
            <h3 className="text-lg font-semibold text-[#0f172a]">New template</h3>
            <p className="text-xs font-medium uppercase tracking-wide text-[#94a3b8]">Workflow</p>
          </div>
          <form onSubmit={handleSubmit} className="mt-4 space-y-4">
            <div className="space-y-1 text-sm text-[#475569]">
              <label className="text-xs font-semibold uppercase tracking-wide text-[#94a3b8]">
                Template name
              </label>
              <input
                value={name}
                onChange={(event) => setName(event.target.value)}
                className="input w-full bg-white"
                placeholder="e.g. Dava workflow"
              />
            </div>

            <div className="space-y-1 text-sm text-[#475569]">
              <label className="text-xs font-semibold uppercase tracking-wide text-[#94a3b8]">
                Case type
              </label>
              <input
                value={caseType}
                onChange={(event) => setCaseType(event.target.value)}
                className="input w-full bg-white"
                placeholder="lawsuit, enforcement, mediation"
              />
            </div>

            <div className="space-y-3">
              <div className="flex items-center justify-between text-sm font-semibold text-[#475569]">
                <span>Steps</span>
                <button
                  type="button"
                  onClick={addStep}
                  className="text-xs font-semibold text-[#2463eb] hover:text-[#1d4fd8]"
                >
                  + Add step
                </button>
              </div>
              <div className="space-y-3">
                {steps.map((step, index) => (
                  <div key={step.id} className="rounded-xl border border-[#e2e8f0] bg-slate-50 p-3">
                    <div className="flex items-start justify-between gap-3">
                      <div className="flex-1 space-y-1">
                        <p className="text-xs font-semibold uppercase tracking-wide text-[#94a3b8]">
                          Step {index + 1}
                        </p>
                        <input
                          value={step.title}
                          onChange={(event) => updateStepTitle(step.id, event.target.value)}
                          className="input w-full bg-white"
                          placeholder="Step title"
                        />
                      </div>
                      <button
                        type="button"
                        onClick={() => removeStep(step.id)}
                        disabled={steps.length === 1}
                        className="text-xs font-semibold text-rose-500 disabled:text-rose-200"
                      >
                        Remove
                      </button>
                    </div>
                    <label className="mt-3 inline-flex items-center gap-2 text-xs text-[#475569]">
                      <input
                        type="checkbox"
                        checked={step.isRequired}
                        onChange={(event) => updateStepRequired(step.id, event.target.checked)}
                        className="h-4 w-4 rounded border border-[#cbd5f5] text-[#2463eb] focus:ring-[#2463eb]"
                      />
                      Required
                    </label>
                  </div>
                ))}
              </div>
            </div>

            <button
              type="submit"
              disabled={isSaving}
              className="w-full rounded-lg bg-[#2463eb] px-4 py-2 text-sm font-semibold text-white hover:bg-[#1d4fd8] disabled:opacity-60"
            >
              {isSaving ? 'Saving…' : 'Create template'}
            </button>
          </form>
        </div>

        <div className="space-y-4">
          <div className="rounded-2xl border border-[#E2E8F0] bg-white p-5 shadow-sm">
            <div className="flex items-center justify-between">
              <h3 className="text-lg font-semibold text-[#0f172a]">Existing templates</h3>
              <p className="text-xs font-semibold uppercase tracking-wide text-[#94a3b8]">
                {templates.length} saved
              </p>
            </div>

            {isLoading ? (
              <p className="mt-3 text-sm text-[#64748b]">Templates are loading...</p>
            ) : templates.length === 0 ? (
              <p className="mt-3 text-sm text-[#64748b]">
                No workflow templates found. Create one to start tracking step progress.
              </p>
            ) : (
              <div className="mt-4 space-y-4">
                {templates.map((template: any) => (
                  <article key={template.id} className="rounded-xl border border-[#E2E8F0] bg-slate-50 p-4">
                    <div className="flex items-center justify-between">
                      <h4 className="text-base font-semibold text-[#0f172a]">{template.name}</h4>
                      <span className="text-xs font-semibold text-[#475569]">{template.case_type}</span>
                    </div>
                    <p className="text-xs text-[#64748b]">Steps: {template.steps?.length ?? 0}</p>
                    <ul className="mt-3 space-y-2 text-sm text-[#475569]">
                      {(template.steps ?? []).map((step: any) => (
                        <li
                          key={step.id}
                          className="flex items-center justify-between border-b border-[#e5e7eb] pb-1 text-xs last:border-0 last:pb-0"
                        >
                          <span>{step.title}</span>
                          <span className="font-semibold text-[#94a3b8]">
                            {step.is_required ? 'Required' : 'Optional'}
                          </span>
                        </li>
                      ))}
                    </ul>
                  </article>
                ))}
              </div>
            )}
          </div>
        </div>
      </div>
    </section>
  );
};
