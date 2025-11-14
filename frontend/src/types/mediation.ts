export type MediationStatus = 'devam' | 'anlasma' | 'anlasmadi';

export interface MediationParty {
  id: string;
  type: 'basvuran' | 'karsi';
  name: string;
  identifier: string;
  phone: string;
  email: string;
  roleDescription?: string;
}

export interface MediationMeeting {
  id: string;
  date: string;
  time: string;
  location: string;
  attendees: string[];
  result: string;
  notes?: string;
}

export interface MediationDocumentVersion {
  id: string;
  version: string;
  uploadedAt: string;
  uploadedBy: string;
}

export interface MediationDocument {
  id: string;
  name: string;
  type: string;
  uploadedBy: string;
  uploadedAt: string;
  versions: MediationDocumentVersion[];
}

export interface MediationNote {
  id: string;
  author: string;
  content: string;
  createdAt: string;
}

export interface Mediation {
  id: string;
  code: string;
  subject: string;
  description: string;
  status: MediationStatus;
  applicants: MediationParty[];
  respondents: MediationParty[];
  assignedLawyer: string;
  applicationDate: string;
  lastMeetingDate?: string;
  requests?: string[];
  meetings: MediationMeeting[];
  documents: MediationDocument[];
  notes: MediationNote[];
}

export interface NewMediationPayload {
  subject: string;
  description: string;
  assignedLawyer: string;
  applicationDate: string;
  applicants: MediationParty[];
  respondents: MediationParty[];
  requests: string[];
  status?: MediationStatus;
}
