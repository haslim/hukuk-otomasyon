import { useForm } from 'react-hook-form';
import { ClientApi } from '../../api/modules/clients';
import { useAsyncData } from '../../hooks/useAsyncData';

export const ClientsPage = () => {
  const { data, refetch } = useAsyncData(['clients'], ClientApi.list);
  const { register, handleSubmit, reset } = useForm();

  const onSubmit = handleSubmit(async (values) => {
    await ClientApi.create(values);
    reset();
    refetch();
  });

  return (
    <section className="space-y-6">
      <header>
        <h2 className="text-2xl font-semibold">Müvekkiller</h2>
        <p className="text-slate-600">CRM kayıtlarını yönetin.</p>
      </header>

      <form onSubmit={onSubmit} className="grid grid-cols-4 gap-4 rounded bg-white p-4 shadow">
        <input className="input" placeholder="Ad Soyad" {...register('name', { required: true })} />
        <select className="input" {...register('type')}> 
          <option value="real">Gerçek</option>
          <option value="legal">Tüzel</option>
        </select>
        <input className="input" placeholder="Telefon" {...register('phone')} />
        <button className="rounded bg-indigo-600 px-4 py-2 text-white" type="submit">
          Kaydet
        </button>
      </form>

      <table className="w-full rounded bg-white text-left shadow">
        <thead>
          <tr>
            <th className="p-3">Adı</th>
            <th className="p-3">Tür</th>
            <th className="p-3">Telefon</th>
          </tr>
        </thead>
        <tbody>
          {data?.map((client: any) => (
            <tr key={client.id} className="border-t">
              <td className="p-3">{client.name}</td>
              <td className="p-3">{client.type}</td>
              <td className="p-3">{client.phone}</td>
            </tr>
          ))}
        </tbody>
      </table>
    </section>
  );
};
