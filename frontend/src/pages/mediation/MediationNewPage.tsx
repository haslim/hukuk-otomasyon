import { useNavigate } from 'react-router-dom';
import { MediationStepper } from '../../components/mediation/MediationStepper';
import { mediationService } from '../../services/mediationService';
import { NewMediationPayload } from '../../types/mediation';

export const MediationNewPage = () => {
  const navigate = useNavigate();

  const handleSubmit = async (payload: NewMediationPayload) => {
    const created = await mediationService.createMediation({
      ...payload,
      status: 'devam',
    });
    navigate(`/mediation/${created.id}`);
  };

  return (
    <section className="space-y-6">
      <div>
        <h1 className="text-3xl font-bold text-slate-900">Yeni Arabuluculuk Başvurusu</h1>
        <p className="text-sm text-slate-500">Adımları tamamlayarak başvuruyu oluşturun ve kaydedin.</p>
      </div>
      <MediationStepper onSubmit={handleSubmit} />
    </section>
  );
};
