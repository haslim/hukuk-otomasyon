import { Mediation, MediationDocument, MediationMeeting, MediationNote, MediationParty, NewMediationPayload } from '../types/mediation';

const createId = () => Math.random().toString(36).substring(2, 10);

const now = () => new Date().toISOString();

let mediations: Mediation[] = [
  {
    id: '1',
    code: 'MED-2025-001',
    subject: 'İşçilik alacağı',
    description: 'Müvekkil Ali Veli ile XYZ Ltd. Şti. arasındaki iş akdinden doğan alacak uyuşmazlığı.',
    status: 'devam',
    applicants: [
      {
        id: createId(),
        type: 'basvuran',
        name: 'Ali Veli',
        identifier: '12345678901',
        phone: '+90 532 000 0000',
        email: 'ali.veli@example.com',
        roleDescription: 'Çalışan',
      },
    ],
    respondents: [
      {
        id: createId(),
        type: 'karsi',
        name: 'XYZ Ltd. Şti.',
        identifier: '1234567890',
        phone: '+90 212 444 0000',
        email: 'info@xyz.com',
        roleDescription: 'İşveren',
      },
    ],
    assignedLawyer: 'Av. Selin Kaya',
    applicationDate: '2025-01-08',
    lastMeetingDate: '2025-01-22',
    requests: ['İhbar tazminatı', 'Fazla mesai alacağı'],
    meetings: [
      {
        id: createId(),
        date: '2025-01-15',
        time: '10:00',
        location: 'BGAofis - Toplantı Salonu 2',
        attendees: ['Ali Veli', 'Av. Selin Kaya', 'XYZ Yetkilisi'],
        result: 'Devam Ediyor',
        notes: 'Taraflar ek belge hazırlayacak.',
      },
      {
        id: createId(),
        date: '2025-01-22',
        time: '14:00',
        location: 'UYAP Arabuluculuk Merkezi',
        attendees: ['Ali Veli', 'Av. Selin Kaya', 'XYZ Ltd. Şti.'],
        result: 'Erteleme',
        notes: 'Karşı taraf teklif sunacak.',
      },
    ],
    documents: [
      {
        id: createId(),
        name: 'Başvuru Formu',
        type: 'Başvuru',
        uploadedBy: 'Av. Selin Kaya',
        uploadedAt: '2025-01-08T09:30:00',
        versions: [
          {
            id: createId(),
            version: 'v1',
            uploadedAt: '2025-01-08T09:30:00',
            uploadedBy: 'Av. Selin Kaya',
          },
        ],
      },
      {
        id: createId(),
        name: 'İlk Toplantı Tutanağı',
        type: 'Tutanak',
        uploadedBy: 'Av. Selin Kaya',
        uploadedAt: '2025-01-15T12:00:00',
        versions: [
          {
            id: createId(),
            version: 'v1',
            uploadedAt: '2025-01-15T12:00:00',
            uploadedBy: 'Av. Selin Kaya',
          },
        ],
      },
    ],
    notes: [
      {
        id: createId(),
        author: 'Av. Selin Kaya',
        content: 'Karşı tarafın teklifini bekliyoruz. İlgili belgeler hazır.',
        createdAt: '2025-01-16T08:30:00',
      },
    ],
  },
  {
    id: '2',
    code: 'MED-2025-002',
    subject: 'Ticari alacak uyuşmazlığı',
    description: 'ABC A.Ş. ile Delta İnşaat arasındaki fatura bedeli ihtilafı.',
    status: 'anlasma',
    applicants: [
      {
        id: createId(),
        type: 'basvuran',
        name: 'ABC A.Ş.',
        identifier: '4657891234',
        phone: '+90 312 555 0101',
        email: 'hukuk@abc.com',
        roleDescription: 'Yüklenici',
      },
    ],
    respondents: [
      {
        id: createId(),
        type: 'karsi',
        name: 'Delta İnşaat',
        identifier: '8765432109',
        phone: '+90 232 444 0909',
        email: 'delta@insaat.com',
        roleDescription: 'İşveren',
      },
    ],
    assignedLawyer: 'Av. Murat Demir',
    applicationDate: '2025-02-02',
    lastMeetingDate: '2025-02-18',
    requests: ['Fatura bedeli', 'Temerrüt faizi'],
    meetings: [
      {
        id: createId(),
        date: '2025-02-10',
        time: '11:00',
        location: 'BGAofis',
        attendees: ['ABC A.Ş.', 'Delta İnşaat', 'Av. Murat Demir'],
        result: 'Devam Ediyor',
      },
      {
        id: createId(),
        date: '2025-02-18',
        time: '16:00',
        location: 'Çevrimiçi Görüşme',
        attendees: ['ABC A.Ş.', 'Delta İnşaat', 'Av. Murat Demir'],
        result: 'Anlaşma Sağlandı',
        notes: 'Tüm talepler üzerinde mutabakat sağlandı.',
      },
    ],
    documents: [
      {
        id: createId(),
        name: 'Anlaşma Belgesi',
        type: 'Sözleşme',
        uploadedBy: 'Av. Murat Demir',
        uploadedAt: '2025-02-18T18:45:00',
        versions: [
          {
            id: createId(),
            version: 'v1',
            uploadedAt: '2025-02-18T18:45:00',
            uploadedBy: 'Av. Murat Demir',
          },
        ],
      },
    ],
    notes: [
      {
        id: createId(),
        author: 'Av. Murat Demir',
        content: 'Taraflar anlaşma metnini imzaladı.',
        createdAt: '2025-02-18T19:00:00',
      },
    ],
  },
];

const updateMediation = (updated: Mediation) => {
  mediations = mediations.map((item) => (item.id === updated.id ? updated : item));
  return updated;
};

const nextCode = () => {
  const seq = mediations.length + 1;
  const year = new Date().getFullYear();
  return `MED-${year}-${String(seq).padStart(3, '0')}`;
};

type MeetingPayload = Omit<MediationMeeting, 'id'>;
type PartyPayload = Omit<MediationParty, 'id'>;
type DocumentPayload = {
  name: string;
  type: string;
  uploadedBy: string;
};
type NotePayload = {
  author: string;
  content: string;
};

export const mediationService = {
  getMediations: () => Promise.resolve(mediations),
  getMediationById: (id: string) => Promise.resolve(mediations.find((item) => item.id === id) ?? null),
  createMediation: (payload: NewMediationPayload) => {
    const newMediation: Mediation = {
      id: createId(),
      code: nextCode(),
      status: payload.status ?? 'devam',
      meetings: [],
      documents: [],
      notes: [],
      lastMeetingDate: undefined,
      ...payload,
    };
    mediations = [newMediation, ...mediations];
    return Promise.resolve(newMediation);
  },
  addMeeting: (mediationId: string, meetingData: MeetingPayload) => {
    const mediation = mediations.find((item) => item.id === mediationId);
    if (!mediation) {
      return Promise.resolve(null);
    }
    const meeting: MediationMeeting = { ...meetingData, id: createId() };
    mediation.meetings = [meeting, ...mediation.meetings];
    mediation.lastMeetingDate = meeting.date;
    updateMediation(mediation);
    return Promise.resolve(meeting);
  },
  addParty: (mediationId: string, partyData: PartyPayload) => {
    const mediation = mediations.find((item) => item.id === mediationId);
    if (!mediation) {
      return Promise.resolve(null);
    }
    const party: MediationParty = { ...partyData, id: createId() };
    if (party.type === 'basvuran') {
      mediation.applicants = [...mediation.applicants, party];
    } else {
      mediation.respondents = [...mediation.respondents, party];
    }
    updateMediation(mediation);
    return Promise.resolve(party);
  },
  uploadDocument: (mediationId: string, documentData: DocumentPayload) => {
    const mediation = mediations.find((item) => item.id === mediationId);
    if (!mediation) {
      return Promise.resolve(null);
    }
    const document: MediationDocument = {
      id: createId(),
      name: documentData.name,
      type: documentData.type,
      uploadedBy: documentData.uploadedBy,
      uploadedAt: now(),
      versions: [
        {
          id: createId(),
          version: 'v1',
          uploadedAt: now(),
          uploadedBy: documentData.uploadedBy,
        },
      ],
    };
    mediation.documents = [document, ...mediation.documents];
    updateMediation(mediation);
    return Promise.resolve(document);
  },
  addNote: (mediationId: string, noteData: NotePayload) => {
    const mediation = mediations.find((item) => item.id === mediationId);
    if (!mediation) {
      return Promise.resolve(null);
    }
    const note: MediationNote = { id: createId(), createdAt: now(), ...noteData };
    mediation.notes = [note, ...mediation.notes];
    updateMediation(mediation);
    return Promise.resolve(note);
  },
};
