<?php
require_once __DIR__ . '/includes/database.php';

function aep_domain_profiles(): array
{
    $profiles = [
        'human_rights' => [
            'label' => 'Human Rights',
            'table' => 'human_rights_cases',
            'summary_fields' => ['title', 'right_violated', 'claimant', 'respondent', 'article_section', 'remedy', 'status'],
            'required_fields' => ['title', 'claimant', 'respondent', 'article_section'],
            'issue_fields' => ['grounds', 'violation_date', 'summary'],
            'arguments' => [
                'Merits turn on the identified right, proof of interference, and whether the respondent is a public authority.',
                'Check whether domestic remedies were exhausted and whether the claim is within time.',
                'Treat remedy selection as a strategic decision: declaration, damages, injunction, release or policy change.',
            ],
            'next_steps' => [
                'Collect the decision, correspondence, medical proof and witness evidence.',
                'Confirm limitation and standing before drafting.',
                'Prepare a pre-action letter and a concise facts chronology.',
            ],
        ],
        'admin_law' => [
            'label' => 'Administrative Law',
            'table' => 'admin_law_cases',
            'summary_fields' => ['title', 'decision_maker', 'claimant', 'respondent', 'ground_of_review', 'relief_sought', 'status'],
            'required_fields' => ['title', 'decision_maker', 'ground_of_review', 'relief_sought'],
            'issue_fields' => ['decision_date', 'summary', 'grounds_of_challenge'],
            'arguments' => [
                'Focus on legality, rationality, procedural fairness, legitimate expectation and proportionality.',
                'Check urgency, permission risk, alternative remedy and limitation issues.',
                'Frame the relief sought clearly as quashing, mandatory, prohibiting or declaratory relief.',
            ],
            'next_steps' => [
                'Obtain the decision record and the full reasons for the decision.',
                'Prepare the pre-action protocol letter and any urgency note.',
                'Map each ground of review to a supporting fact and authority.',
            ],
        ],
        'tort' => [
            'label' => 'Tort Law',
            'table' => 'tort_cases',
            'summary_fields' => ['title', 'tort_type', 'claimant_name', 'defendant_name', 'incident_date', 'damages_claimed', 'status'],
            'required_fields' => ['title', 'tort_type', 'claimant_name', 'defendant_name', 'incident_date'],
            'issue_fields' => ['injury_description', 'duty_of_care', 'breach', 'damages_claimed'],
            'arguments' => [
                'Structure the case around duty, breach, causation, remoteness and damages.',
                'Identify contributory negligence, mitigation and any expert evidence required.',
                'Make sure the loss schedule is consistent with the pleaded facts.',
            ],
            'next_steps' => [
                'Gather medical, technical and documentary evidence.',
                'Prepare a detailed schedule of loss.',
                'Confirm whether a protocol letter or urgent interim step is required.',
            ],
        ],
        'oil_gas' => [
            'label' => 'Oil and Gas',
            'table' => 'oil_gas_cases',
            'summary_fields' => ['title', 'licence_number', 'operator', 'regulatory_body', 'dispute_type', 'status'],
            'required_fields' => ['title', 'licence_number', 'operator'],
            'issue_fields' => ['contract_type', 'dispute_description', 'legal_basis'],
            'arguments' => [
                'Review regulatory compliance, contractual obligations and sector-specific risk allocation.',
                'Check licence validity, operating rights, environmental duties and dispute resolution clauses.',
                'Consider technical evidence, compliance chronology and commercial leverage points.',
            ],
            'next_steps' => [
                'Verify the licence and permit record.',
                'Build the contract and regulation timeline.',
                'Collect technical and environmental evidence where relevant.',
            ],
        ],
        'commercial_law' => [
            'label' => 'Commercial Law',
            'table' => 'commercial_law_cases',
            'summary_fields' => ['case_reference', 'case_type', 'client_name', 'counterparty_name', 'transaction_date', 'value_at_risk', 'status'],
            'required_fields' => ['case_reference', 'client_name', 'counterparty_name', 'transaction_type'],
            'issue_fields' => ['transaction_description', 'dispute_summary', 'legal_basis', 'commercial_context'],
            'arguments' => [
                'Assess the commercial transaction, governing law, implied terms and whether terms are entire agreement or subject to custom.',
                'Review the dispute against the contract hierarchy, regulatory context and industry standards.',
                'Consider force majeure, termination rights, indemnification and any mitigation or offset opportunities.',
            ],
            'next_steps' => [
                'Collect all transaction documents, correspondence and versions.',
                'Prepare a commercial context summary and regulatory compliance check.',
                'Map liability exposure, remedy options and any urgent interim measures needed.',
            ],
        ],
        'contract' => [
            'label' => 'Contract Law',
            'table' => 'contract_cases',
            'summary_fields' => ['case_reference', 'case_type', 'client_name', 'opponent_name', 'contract_date', 'damages_claimed', 'status'],
            'required_fields' => ['case_reference', 'client_name', 'opponent_name', 'contract_description'],
            'issue_fields' => ['contract_description', 'breach_description', 'legal_basis', 'without_prejudice'],
            'arguments' => [
                'Test formation, certainty, capacity, consideration and whether the contract was enforceable on the facts.',
                'Examine performance, breach, mitigation and remoteness of loss with the contract documents at the centre.',
                'Consider whether a settlement position or injunction is commercially and legally preferable.',
            ],
            'next_steps' => [
                'Collect the full contract, correspondence and amendment trail.',
                'Prepare a chronology of performance and breach.',
                'Map the potential remedies and any settlement posture.',
            ],
        ],
        'property' => [
            'label' => 'Property and Landlord Tenant',
            'table' => 'property_cases',
            'summary_fields' => ['case_reference', 'case_type', 'landlord_name', 'tenant_name', 'notice_type', 'possession_status', 'status'],
            'required_fields' => ['case_reference', 'landlord_name', 'tenant_name', 'property_address'],
            'issue_fields' => ['tenancy_type', 'notice_date', 'dispute_summary', 'legal_basis'],
            'arguments' => [
                'Focus on possession, rent arrears, notice validity, tenancy status and documentary compliance.',
                'Check the lease, notice, arrears schedule and possession timeline.',
                'Evaluate landlord and tenant obligations, repairs, breach and mitigation points.',
            ],
            'next_steps' => [
                'Confirm the tenancy and notice chronology.',
                'Collect the lease, rent ledger and correspondence.',
                'Prepare possession and remedies analysis.',
            ],
        ],
        'criminal' => [
            'label' => 'Criminal Law',
            'table' => 'criminal_cases',
            'summary_fields' => ['case_reference', 'defendant_name', 'case_type', 'charges', 'plea', 'status'],
            'required_fields' => ['case_reference', 'defendant_name', 'charges'],
            'issue_fields' => ['offence_date', 'court_name', 'bail_status', 'legal_basis'],
            'arguments' => [
                'Frame the merits around proof, elements of the offence, admissibility and procedural fairness.',
                'Identify plea strategy, bail risk, sentencing exposure and evidential weaknesses.',
                'Make sure the chronology and charge particulars are internally consistent.',
            ],
            'next_steps' => [
                'Review the charge sheet, witness statements and custody record.',
                'Assess bail, disclosure and plea position.',
                'Prepare the defence chronology and note key evidential gaps.',
            ],
        ],
        'immigration' => [
            'label' => 'Immigration Law',
            'table' => 'immigration_cases',
            'summary_fields' => ['case_reference', 'case_type', 'client_name', 'client_nationality', 'decision_date', 'status'],
            'required_fields' => ['case_reference', 'client_name', 'case_type', 'legal_basis'],
            'issue_fields' => ['decision_description', 'appeal_reference', 'removal_date', 'evidence_available'],
            'arguments' => [
                'Assess the immigration route, eligibility, evidential sufficiency and any human rights or family life layer.',
                'Check the decision date, reasons, credibility, refusal grounds and appeal rights.',
                'Ensure the chronology and documentary evidence are aligned with the immigration rules and exceptions.',
            ],
            'next_steps' => [
                'Collect the decision notice, passport record and document chronology.',
                'Review the route requirements and any dependency points.',
                'Prepare the grounds, evidence schedule and next procedural steps.',
            ],
        ],
        'employment' => [
            'label' => 'Employment Law',
            'table' => 'employment_cases',
            'summary_fields' => ['case_reference', 'case_type', 'claimant_name', 'respondent_name', 'dismissal_reason', 'status'],
            'required_fields' => ['case_reference', 'claimant_name', 'respondent_name', 'dismissal_reason'],
            'issue_fields' => ['claim_unfair_dismissal', 'claim_discrimination', 'disciplinary_process', 'legal_basis'],
            'arguments' => [
                'Focus on fairness, reasonableness, procedural compliance and any discrimination or whistleblowing element.',
                'Review continuity, contractual terms, ACAS compliance and the evidential record.',
                'Consider settlement posture, remedy exposure and any interim steps or UfW issues.',
            ],
            'next_steps' => [
                'Check the contract, disciplinary record and ACAS position.',
                'Prepare the chronology of dismissal and the evidence bundle.',
                'Map the claim basis and settlement or hearing strategy.',
            ],
        ],
        'family' => [
            'label' => 'Family Law',
            'table' => 'family_cases',
            'summary_fields' => ['title', 'case_type', 'petitioner', 'respondent', 'court', 'status'],
            'required_fields' => ['title', 'petitioner', 'respondent', 'relief'],
            'issue_fields' => ['children', 'summary', 'court', 'legal_basis'],
            'arguments' => [
                'Structure the advice around children, welfare, parental responsibility and the best interests standard.',
                'Assess interim relief, risk of harm, factual disputes and evidence credibility.',
                'Keep the strategy practical and child-centred, with a clear route to settlement or hearing.',
            ],
            'next_steps' => [
                'Collect the factual chronology, documents and any risk flags.',
                'Clarify the relief sought and the practical objectives.',
                'Prepare a case summary for settlement or court directions.',
            ],
        ],
        'latin_maxims' => [
            'label' => 'Latin Maxims',
            'table' => 'latin_maxims',
            'summary_fields' => ['maxim', 'meaning', 'category'],
            'required_fields' => ['maxim', 'meaning'],
            'issue_fields' => ['details', 'category'],
            'arguments' => [
                'Use the maxim to support principle, fairness and interpretive clarity without displacing the statutory or contractual analysis.',
                'Match the maxim to the precise legal context: procedural fairness, equity, evidence, or remedies.',
                'Explain the maxim in practical terms and tie it to the facts, not merely as an ornamental quotation.',
            ],
            'next_steps' => [
                'Select the maxim that best supports the key proposition.',
                'Pair it with a fact-specific explanation and a legal authority if available.',
                'Use it as a persuasive framing device in advisory notes and drafting.',
            ],
        ],
        'international_arbitration' => [
            'label' => 'International Investment & Arbitration',
            'table' => 'international_arbitration_cases',
            'summary_fields' => ['case_reference', 'investor_name', 'respondent_state', 'investment_description', 'dispute_amount', 'status'],
            'required_fields' => ['case_reference', 'investor_name', 'respondent_state', 'treaty_basis'],
            'issue_fields' => ['investment_description', 'dispute_summary', 'regulatory_action', 'damages_claimed'],
            'arguments' => [
                'Map the investment and regulatory action to the relevant bilateral or multilateral investment treaty framework (BIT, FTA, ECT, etc.).',
                'Analyse the alleged breach against the applicable treaty standard of treatment (FET, FPS, NT, MFN, expropriation/indirect expropriation).',
                'Assess compensability, valuation methodology, causation and any available defences (emergency/security exception, police powers doctrine, necessity).',
            ],
            'next_steps' => [
                'Gather the investment documentation, regulatory decisions and supporting correspondence.',
                'Prepare the preliminary objections/admissibility position on treaty coverage, standing and jurisdiction.',
                'Build the treaty breach and damages narrative with expert economic and industry evidence.',
            ],
        ],
    ];

    return aep_enrich_domain_profiles($profiles);
}

function aep_domain_library_extensions(): array
{
    return [
        'human_rights' => [
            'subdomains' => ['Civil liberties', 'Police powers', 'Detention and prison rights', 'Discrimination and equality', 'Privacy and surveillance', 'Freedom of expression and assembly'],
            'templates' => [
                'violation_assessment' => ['label' => 'Rights Violation Assessment', 'sections' => ['Protected right and scope', 'Interference facts', 'Justification test', 'Remedy path']],
                'urgent_injunction_note' => ['label' => 'Urgent Injunction Note', 'sections' => ['Urgency basis', 'Irreparable harm', 'Balance of convenience', 'Interim order sought']],
                'proportionality_analysis' => ['label' => 'Proportionality Analysis', 'sections' => ['Legitimate aim', 'Rational connection', 'Least intrusive means', 'Fair balance conclusion']],
                'state_liability_brief' => ['label' => 'State Liability Brief', 'sections' => ['Public authority act', 'Rights impact', 'Causation and damage', 'Remedy and declarations']],
                'interim_relief_checklist' => ['label' => 'Interim Relief Checklist', 'sections' => ['Threshold test', 'Evidence matrix', 'Undertakings and risk', 'Draft order points']],
                'human_rights_permission_appeal' => ['label' => 'Permission to Appeal Note', 'sections' => ['Appealable error', 'Threshold of arguability', 'Public importance point', 'Relief sought on appeal']],
                'human_rights_trial_bundle_plan' => ['label' => 'Human Rights Trial Bundle Plan', 'sections' => ['Core pleadings index', 'Authorities and jurisprudence set', 'Witness and expert materials', 'Chronology and issue map']],
                'human_rights_jr_form_pack' => ['label' => 'Judicial Review Form Pack (Human Rights)', 'sections' => ['Claim form fields checklist', 'Grounds statement summary', 'Urgency and interim relief fields', 'Service and filing compliance']],
                'human_rights_n463_permission' => ['label' => 'N463 Permission Form Draft', 'sections' => ['Order challenged details', 'Grounds of appeal summary', 'Relief sought', 'Supporting evidence index']],
            ],
        ],
        'admin_law' => [
            'subdomains' => ['Judicial review', 'Regulatory enforcement', 'Procurement disputes', 'Public body discipline', 'Licensing and permits', 'Planning and local authority decisions'],
            'templates' => [
                'jr_ground_matrix' => ['label' => 'Judicial Review Ground Matrix', 'sections' => ['Decision under challenge', 'Ground and authority', 'Evidence support', 'Relief requested']],
                'pre_action_protocol' => ['label' => 'Pre-Action Protocol Letter', 'sections' => ['Impugned decision', 'Grounds summary', 'Disclosure request', 'Proposed resolution']],
                'permission_stage_note' => ['label' => 'Permission Stage Note', 'sections' => ['Promptness and standing', 'Arguability test', 'Alternative remedy analysis', 'Protective costs and urgency']],
                'irrationality_brief' => ['label' => 'Irrationality and Fairness Brief', 'sections' => ['Policy or decision error', 'Procedural unfairness points', 'Evidential support', 'Remedial framing']],
                'remedies_schedule' => ['label' => 'Public Law Remedies Schedule', 'sections' => ['Quashing relief', 'Mandatory or prohibiting relief', 'Declaration and interim measures', 'Practical compliance plan']],
                'admin_law_skeleton' => ['label' => 'Administrative Law Skeleton Argument', 'sections' => ['Decision and legal framework', 'Ground-by-ground submissions', 'Evidence references', 'Orders and costs sought']],
                'admin_costs_protective_order' => ['label' => 'Protective Costs Order Note', 'sections' => ['Public interest basis', 'Financial exposure analysis', 'Merits threshold', 'Proposed costs cap terms']],
                'admin_jr_claim_form_pack' => ['label' => 'Administrative JR Claim Form Pack', 'sections' => ['Claimant/defendant particulars', 'Decision details and dates', 'Grounds and remedies fields', 'Service and acknowledgement steps']],
                'admin_n461_statement_pack' => ['label' => 'N461 Statement of Facts and Grounds Pack', 'sections' => ['Facts chronology', 'Grounds by heading', 'Relief and urgency section', 'Documents and authorities list']],
            ],
        ],
        'tort' => [
            'subdomains' => ['Negligence', 'Defamation', 'Nuisance', 'Occupiers liability', 'Professional negligence', 'Product liability'],
            'templates' => [
                'liability_matrix' => ['label' => 'Liability Matrix', 'sections' => ['Duty', 'Breach', 'Causation', 'Damage and defences']],
                'schedule_of_loss' => ['label' => 'Schedule of Loss Draft', 'sections' => ['General damages', 'Special damages', 'Future loss', 'Mitigation record']],
                'duty_breach_note' => ['label' => 'Duty and Breach Note', 'sections' => ['Relationship and duty source', 'Standard of care', 'Breach particulars', 'Comparable authority']],
                'causation_remoteness_brief' => ['label' => 'Causation and Remoteness Brief', 'sections' => ['Factual causation', 'Legal causation', 'Foreseeability and remoteness', 'Intervening acts analysis']],
                'defamation_defence_map' => ['label' => 'Defamation Defence Map', 'sections' => ['Publication and meaning', 'Truth/honest opinion/public interest', 'Serious harm', 'Remedy exposure']],
                'tort_pre_action_letter' => ['label' => 'Tort Pre-Action Letter', 'sections' => ['Duty and breach allegation', 'Loss summary', 'Disclosure requests', 'Settlement timetable']],
                'tort_trial_bundle_index' => ['label' => 'Tort Trial Bundle Index', 'sections' => ['Pleadings and statements', 'Liability evidence', 'Quantum documents', 'Authorities and chronology']],
                'tort_n1_claim_form_pack' => ['label' => 'N1 Claim Form Pack (Tort)', 'sections' => ['Parties and addresses', 'Brief details of claim', 'Value and remedies', 'Issue and service checklist']],
                'tort_n180_directions_pack' => ['label' => 'N180 Directions Questionnaire Pack (Tort)', 'sections' => ['Track proposal', 'Witness and expert entries', 'Unavailable dates', 'Other information box draft']],
            ],
        ],
        'oil_gas' => [
            'subdomains' => ['Farm-in and farm-out', 'Joint Operating Agreements (JOA)', 'Risk allocation and indemnities', 'Mutual hold harmless and insurance', 'Licensing and regulatory compliance', 'Pipeline, transport, and decommissioning'],
            'templates' => [
                'regulatory_compliance_review' => ['label' => 'Regulatory Compliance Review', 'sections' => ['Applicable regime', 'Compliance breaches', 'Operational impact', 'Corrective actions']],
                'operator_dispute_brief' => ['label' => 'Operator Dispute Brief', 'sections' => ['Contract framework', 'Breach events', 'Technical evidence', 'Commercial resolution strategy']],
                'joint_operating_agreement_note' => ['label' => 'JOA Risk Note', 'sections' => ['Operator obligations', 'Cost sharing and accounting', 'Default provisions', 'Dispute pathway']],
                'environmental_incident_response' => ['label' => 'Environmental Incident Response', 'sections' => ['Incident chronology', 'Regulatory notifications', 'Liability containment', 'Remediation plan']],
                'arbitration_readiness_pack' => ['label' => 'Arbitration Readiness Pack', 'sections' => ['Jurisdiction and clause review', 'Claims and defenses', 'Technical expert needs', 'Relief and enforcement strategy']],
                'oilgas_regulatory_response' => ['label' => 'Regulatory Enforcement Response', 'sections' => ['Notice allegations summary', 'Compliance defence position', 'Technical evidence table', 'Corrective and settlement proposals']],
                'oilgas_expert_instruction' => ['label' => 'Oil and Gas Expert Instruction Note', 'sections' => ['Expert scope and questions', 'Data and records provided', 'Methodology expectations', 'Deadline and hearing use']],
                'oilgas_n1_claim_pack' => ['label' => 'N1 Claim Form Pack (Oil and Gas)', 'sections' => ['Parties and contract/dispute summary', 'Value and remedy fields', 'Jurisdiction and forum details', 'Service checklist']],
                'oilgas_n180_case_management' => ['label' => 'N180 Case Management Pack (Oil and Gas)', 'sections' => ['Track and complexity grounds', 'Expert evidence proposal', 'Disclosure scope', 'Hearing estimate']],
            ],
        ],
        'commercial_law' => [
            'subdomains' => ['Commercial contracts', 'Mergers and acquisitions', 'Distribution and agency', 'Technology licensing', 'Franchise agreements', 'Warranty and indemnity claims'],
            'templates' => [
                'commercial_contract_analysis' => ['label' => 'Commercial Contract Analysis', 'sections' => ['Transaction overview', 'Key terms and obligations', 'Risk allocation summary', 'Dispute escalation pathway']],
                'm_and_a_due_diligence_checklist' => ['label' => 'M&A Due Diligence Checklist', 'sections' => ['Corporate structure and ownership', 'Material contracts and licenses', 'Litigation and regulatory status', 'Environmental and compliance matters']],
                'warranty_indemnity_claim' => ['label' => 'Warranty and Indemnity Claim Note', 'sections' => ['Breach identified', 'Contractual basis for recovery', 'Quantum and mitigation', 'Notice and procedure requirements']],
                'commercial_dispute_strategy' => ['label' => 'Commercial Dispute Strategy', 'sections' => ['Dispute chronology', 'Contractual remedies review', 'Litigation vs arbitration analysis', 'Commercial leverage assessment']],
                'distribution_agreement_review' => ['label' => 'Distribution Agreement Review', 'sections' => ['Term and termination rights', 'Exclusivity and territory', 'Performance and compliance obligations', 'Dispute resolution and governing law']],
                'technology_licence_summary' => ['label' => 'Technology Licence Summary', 'sections' => ['Licensed IP scope', 'Restrictions and permitted use', 'Royalty and payment terms', 'Infringement and liability clauses']],
                'commercial_settlement_proposal' => ['label' => 'Commercial Settlement Proposal', 'sections' => ['Commercial context and history', 'Settlement offer and structure', 'Payment terms and schedule', 'Confidentiality and release']],
                'commercial_arbitration_prep' => ['label' => 'Commercial Arbitration Prep Pack', 'sections' => ['Arbitration clause review', 'Procedural rules and seat', 'Claimant and respondent positions', 'Evidence and expert strategy']],
                'commercial_n1_claim_pack' => ['label' => 'N1 Claim Form Pack (Commercial)', 'sections' => ['Parties and transaction details', 'Breach and loss particulars', 'Quantum and remedies sought', 'Interest and contract clauses']],
                'commercial_n180_dq_pack' => ['label' => 'N180 Directions Questionnaire Pack (Commercial)', 'sections' => ['Track and complexity', 'Expert and technical evidence', 'Disclosure scope and disputes', 'Hearing timeline and venue']],
            ],
        ],
        'contract' => [
            'subdomains' => ['Commercial contracts', 'Supply and services', 'Construction contracts', 'Settlement and release', 'Technology and SaaS contracts', 'Consumer and B2B terms'],
            'templates' => [
                'breach_analysis' => ['label' => 'Breach Analysis Note', 'sections' => ['Contract terms', 'Breach events', 'Loss and causation', 'Remedy options']],
                'demand_letter' => ['label' => 'Demand Letter Draft', 'sections' => ['Breach statement', 'Demand and timeline', 'Evidence references', 'Without prejudice posture']],
                'particulars_of_claim_contract' => ['label' => 'Contract Particulars of Claim Outline', 'sections' => ['Parties and contract formation', 'Terms and obligations', 'Breach particulars', 'Loss, interest, relief']],
                'defence_contract_outline' => ['label' => 'Contract Defence Outline', 'sections' => ['Admissions and denials', 'Non-performance and condition precedent', 'Causation and mitigation challenge', 'Alternative valuation']],
                'settlement_term_sheet' => ['label' => 'Settlement Term Sheet', 'sections' => ['Commercial objectives', 'Payment and release terms', 'Confidentiality and non-disparagement', 'Default and enforcement terms']],
                'contract_directions_questionnaire' => ['label' => 'Contract Directions Questionnaire Draft', 'sections' => ['Track proposal and reasons', 'Witness and expert position', 'Unavailable dates', 'Case management directions sought']],
                'contract_reply_to_defence' => ['label' => 'Contract Reply to Defence Draft', 'sections' => ['Point-by-point admissions/denials', 'Response to breach defence', 'Quantum and mitigation reply', 'Relief maintained']],
                'contract_n1_claim_pack' => ['label' => 'N1 Claim Form Pack (Contract)', 'sections' => ['Parties and service addresses', 'Brief claim particulars', 'Value and interest claims', 'Statement of truth and filing checks']],
                'contract_n9b_defence_pack' => ['label' => 'N9B Defence Pack (Contract)', 'sections' => ['Admissions and denials fields', 'Defence facts summary', 'Counterclaim section prompts', 'Filing deadlines and service']],
                'contract_n180_dq_pack' => ['label' => 'N180 Directions Questionnaire Pack (Contract)', 'sections' => ['Mediation election', 'Track and court venue', 'Witness/expert entries', 'Other information draft']],
            ],
        ],
        'property' => [
            'subdomains' => ['Residential tenancy', 'Commercial lease', 'Possession proceedings', 'Conveyancing disputes', 'Service charge and disrepair', 'Boundary and title disputes'],
            'templates' => [
                'notice_validity_check' => ['label' => 'Notice Validity Checklist', 'sections' => ['Tenancy status', 'Notice content', 'Service evidence', 'Timeline compliance']],
                'possession_case_plan' => ['label' => 'Possession Case Plan', 'sections' => ['Arrears schedule', 'Grounds for possession', 'Evidence bundle', 'Hearing preparation']],
                'lease_breach_schedule' => ['label' => 'Lease Breach Schedule', 'sections' => ['Covenant terms', 'Breach incidents', 'Notice and cure steps', 'Remedy and forfeiture analysis']],
                'disrepair_evidence_pack' => ['label' => 'Disrepair Evidence Pack', 'sections' => ['Defect log and chronology', 'Inspection and expert evidence', 'Notice and landlord response', 'Quantum for damages']],
                'title_dispute_strategy' => ['label' => 'Title Dispute Strategy', 'sections' => ['Title documents and plans', 'Factual possession history', 'Legal basis and authorities', 'Relief and settlement options']],
                'property_section8_section21' => ['label' => 'Section 8/Section 21 Pathway Plan', 'sections' => ['Grounds and notice route choice', 'Service and timing checks', 'Defence risk points', 'Possession hearing route']],
                'property_trial_bundle_plan' => ['label' => 'Property Trial Bundle Plan', 'sections' => ['Tenancy and lease documents', 'Notice/service proof', 'Arrears/disrepair evidence', 'Authorities and draft order']],
                'property_n5b_possession_pack' => ['label' => 'N5B Possession Claim Pack', 'sections' => ['Property and tenancy details', 'Notice and compliance fields', 'Arrears/rent schedule', 'Service and issue checklist']],
                'property_n11m_defence_pack' => ['label' => 'N11M Defence Pack (Possession)', 'sections' => ['Tenant response fields', 'Disrepair/set-off details', 'Housing and hardship factors', 'Supporting evidence list']],
            ],
        ],
        'criminal' => [
            'subdomains' => ['Summary offences', 'Indictable offences', 'Bail and custody', 'Sentencing strategy', 'Disclosure and admissibility', 'Appeal against conviction or sentence'],
            'templates' => [
                'defence_theory' => ['label' => 'Defence Theory Outline', 'sections' => ['Elements challenged', 'Evidence weaknesses', 'Defence witnesses', 'Cross-examination themes']],
                'bail_submission' => ['label' => 'Bail Submission Draft', 'sections' => ['Community ties', 'Risk rebuttal', 'Conditions proposed', 'Authority support']],
                'cross_examination_plan' => ['label' => 'Cross-Examination Plan', 'sections' => ['Prosecution witness profile', 'Credibility points', 'Inconsistency sequence', 'Concession objectives']],
                'sentencing_mitigation_note' => ['label' => 'Sentencing Mitigation Note', 'sections' => ['Personal mitigation', 'Offence context', 'Rehabilitation and risk', 'Proposed sentence framework']],
                'evidence_admissibility_challenge' => ['label' => 'Evidence Admissibility Challenge', 'sections' => ['Evidence item and source', 'Grounds of exclusion', 'Prejudice and fairness', 'Alternative submission']],
                'criminal_case_statement_response' => ['label' => 'Defence Case Statement Response', 'sections' => ['Prosecution assertions map', 'Disclosure deficiencies', 'Defence factual response', 'Further disclosure requests']],
                'criminal_appeal_notice' => ['label' => 'Criminal Appeal Notice Draft', 'sections' => ['Conviction/sentence challenged', 'Grounds of appeal', 'Miscarriage indicators', 'Relief sought']],
                'criminal_bail_variation_form_pack' => ['label' => 'Bail Variation Form Pack', 'sections' => ['Current bail terms', 'Variation sought', 'Risk rebuttal evidence', 'Proposed safeguards']],
                'criminal_appeal_form_pack' => ['label' => 'Criminal Appeal Form Pack', 'sections' => ['Order or conviction challenged', 'Grounds entries', 'Transcript/evidence references', 'Relief requested']],
            ],
        ],
        'immigration' => [
            'subdomains' => ['Work and skilled worker routes', 'Family and Article 8 routes', 'Asylum and protection', 'Appeals and judicial review', 'Sponsor licence and compliance', 'Detention, bail, and removal challenges'],
            'templates' => [
                'eligibility_assessment' => ['label' => 'Eligibility Assessment', 'sections' => ['Route requirements', 'Evidence sufficiency', 'Refusal risks', 'Action plan']],
                'appeal_ground_outline' => ['label' => 'Appeal Ground Outline', 'sections' => ['Decision errors', 'Factual corrections', 'Legal framework', 'Relief requested']],
                'credibility_matrix' => ['label' => 'Credibility Matrix', 'sections' => ['Core narrative points', 'Corroborating documents', 'Potential inconsistencies', 'Explanatory responses']],
                'article8_balancing_note' => ['label' => 'Article 8 Balancing Note', 'sections' => ['Private and family life facts', 'Public interest factors', 'Proportionality assessment', 'Relief framing']],
                'detention_bail_strategy' => ['label' => 'Detention and Bail Strategy', 'sections' => ['Lawfulness challenge', 'Risk factors', 'Community support evidence', 'Proposed conditions and timetable']],
                'immigration_jr_grounds' => ['label' => 'Immigration Judicial Review Grounds', 'sections' => ['Impugned decision and timeline', 'Public law error grounds', 'Interim relief and urgency', 'Relief and costs position']],
                'immigration_bundle_plan' => ['label' => 'First-tier/Upper Tribunal Bundle Plan', 'sections' => ['Core identity and status records', 'Decision and refusal analysis', 'Witness and expert materials', 'Authorities and chronology']],
                'immigration_iaft_form_pack' => ['label' => 'IAFT Appeal Form Pack', 'sections' => ['Decision and appellant details', 'Grounds and human rights entries', 'Evidence checklist', 'Fee/waiver and filing route']],
                'immigration_jr_form_pack' => ['label' => 'Immigration JR Form Pack', 'sections' => ['Claim form completion', 'Urgency/interim relief fields', 'Grounds statement structure', 'Service and acknowledgement rules']],
            ],
        ],
        'employment' => [
            'subdomains' => ['Unfair dismissal', 'Discrimination', 'Whistleblowing', 'Wage and contract claims', 'Redundancy and TUPE', 'Restrictive covenants'],
            'templates' => [
                'tribunal_claim_plan' => ['label' => 'Tribunal Claim Plan', 'sections' => ['Cause of action', 'Limitation', 'Evidence and witnesses', 'Remedy position']],
                'settlement_strategy' => ['label' => 'Settlement Strategy Note', 'sections' => ['Commercial objectives', 'Liability risk', 'Without prejudice terms', 'Negotiation sequencing']],
                'et1_particulars_outline' => ['label' => 'ET1 Particulars Outline', 'sections' => ['Employment relationship facts', 'Unlawful acts pleaded', 'Chronology and comparators', 'Loss and remedy claims']],
                'disciplinary_process_audit' => ['label' => 'Disciplinary Process Audit', 'sections' => ['Policy framework', 'Procedural steps taken', 'Fairness and bias checks', 'Outcome vulnerability analysis']],
                'schedule_of_loss_employment' => ['label' => 'Employment Schedule of Loss', 'sections' => ['Past loss of earnings', 'Future loss and pension', 'Injury to feelings and aggravated damages', 'Mitigation and benefits received']],
                'employment_case_management_agenda' => ['label' => 'Employment Case Management Agenda', 'sections' => ['List of issues', 'Disclosure categories', 'Witness evidence timetable', 'Hearing length and logistics']],
                'employment_list_of_issues' => ['label' => 'Employment List of Issues', 'sections' => ['Claims and legal tests', 'Disputed facts', 'Comparator and causation points', 'Remedy questions for determination']],
                'employment_et1_form_pack' => ['label' => 'ET1 Claim Form Pack', 'sections' => ['Parties and ACAS details', 'Claim particulars headings', 'Remedy requests', 'Time limit and submission checks']],
                'employment_et3_response_pack' => ['label' => 'ET3 Response Form Pack', 'sections' => ['Response admissions/denials', 'Jurisdictional objections', 'Limitation response', 'Counter-schedule preparation']],
            ],
        ],
        'family' => [
            'subdomains' => ['Divorce and dissolution', 'Child arrangements', 'Financial remedies', 'Domestic abuse protection', 'Care proceedings', 'International relocation and abduction'],
            'templates' => [
                'child_welfare_note' => ['label' => 'Child Welfare Note', 'sections' => ['Welfare factors', 'Risk concerns', 'Interim arrangements', 'Recommended orders']],
                'financial_relief_outline' => ['label' => 'Financial Relief Outline', 'sections' => ['Asset schedule', 'Needs assessment', 'Disclosure gaps', 'Proposed settlement range']],
                'section7_analysis' => ['label' => 'Section 7 Report Analysis', 'sections' => ['Recommendations summary', 'Points of agreement/dispute', 'Welfare checklist alignment', 'Court proposal']],
                'non_molestation_application' => ['label' => 'Non-Molestation Application Plan', 'sections' => ['Incident chronology', 'Risk and urgency', 'Supporting evidence', 'Order terms sought']],
                'case_management_position_statement' => ['label' => 'Case Management Position Statement', 'sections' => ['Current procedural stage', 'Issues for determination', 'Interim applications', 'Directions sought']],
                'family_fhdra_position' => ['label' => 'FHDRA Position Statement', 'sections' => ['Child-focused issues', 'Interim contact proposals', 'Safeguarding concerns', 'Directions and timetable sought']],
                'family_fact_finding_plan' => ['label' => 'Fact-Finding Hearing Plan', 'sections' => ['Allegation schedule', 'Evidence required', 'Witness sequence', 'Findings sought']],
                'family_c100_form_pack' => ['label' => 'C100 Application Form Pack', 'sections' => ['Applicant/child details', 'Orders sought', 'Safeguarding and harm allegations', 'MIAM and urgency fields']],
                'family_fl401_form_pack' => ['label' => 'FL401 Non-Molestation Form Pack', 'sections' => ['Respondent and relationship details', 'Incident particulars', 'Urgency and without notice basis', 'Order terms and service']],
            ],
        ],
        'latin_maxims' => [
            'subdomains' => ['Equity maxims', 'Procedure maxims', 'Evidence maxims', 'Remedy maxims', 'Interpretation maxims', 'Public law maxims'],
            'templates' => [
                'maxim_application_note' => ['label' => 'Maxim Application Note', 'sections' => ['Chosen maxim', 'Meaning in context', 'Fact linkage', 'Authority and caution']],
                'maxim_argument_bank' => ['label' => 'Maxim Argument Bank', 'sections' => ['Primary proposition', 'Supporting maxims', 'Counter-arguments', 'Usage in submissions']],
                'maxim_to_issue_matrix' => ['label' => 'Maxim-to-Issue Matrix', 'sections' => ['Issue statement', 'Candidate maxims', 'Applicability test', 'Preferred submission wording']],
                'maxim_caution_note' => ['label' => 'Maxim Caution Note', 'sections' => ['Overreach risks', 'Modern authority check', 'Context limitations', 'Safe usage guidance']],
                'hearing_oral_points' => ['label' => 'Maxim Oral Hearing Points', 'sections' => ['Opening proposition', 'Maxim support lines', 'Anticipated challenge responses', 'Closing principle statement']],
                'maxim_authority_table' => ['label' => 'Maxim Authority Table', 'sections' => ['Maxim text and translation', 'Leading authority links', 'Jurisdictional relevance', 'Use and limits in argument']],
                'maxim_rebuttal_sheet' => ['label' => 'Maxim Rebuttal Sheet', 'sections' => ['Opponent maxim reliance', 'Misapplication points', 'Counter-authorities', 'Alternative principle framing']],
                'maxim_authorities_bundle_pack' => ['label' => 'Maxim Authorities Bundle Pack', 'sections' => ['Primary authorities selection', 'Parallel modern authority', 'Citation and pinpoint references', 'Hearing bundle placement']],
                'maxim_judicial_notice_note' => ['label' => 'Maxim Judicial Notice Note', 'sections' => ['Proposition requiring support', 'Appropriate maxim usage test', 'Limitations and caveats', 'Suggested oral submission text']],
                'maxim_pronunciation_sheet' => ['label' => 'Maxim Pronunciation Sheet', 'sections' => ['Maxim text and syllable split', 'Approx phonetic line', 'Courtroom delivery pacing', 'Meaning linkage sentence']],
                'maxim_oral_delivery_card' => ['label' => 'Maxim Oral Delivery Card', 'sections' => ['Opening pronunciation line', 'Translation follow-up', 'Authority anchor phrase', 'Closing proposition']],
            ],
        ],
        'international_arbitration' => [
            'subdomains' => ['Investor-State Disputes (ISDS)', 'Treaty Arbitration', 'Commercial Arbitration', 'Preliminary Objections', 'Merits Phase', 'Post-Award/Enforcement'],
            'templates' => [
                'treaty_breach_analysis' => ['label' => 'Treaty Breach Analysis', 'sections' => ['Treaty framework and scope', 'Alleged regulatory action', 'Standard of treatment assessment', 'Causation and injury', 'Defences available']],
                'notice_of_arbitration_draft' => ['label' => 'Notice of Arbitration Draft', 'sections' => ['Claimant and respondent identification', 'Factual background', 'Legal basis and treaty claims', 'Relief sought', 'Seat and procedural choices']],
                'preliminary_objections_brief' => ['label' => 'Preliminary Objections Brief', 'sections' => ['Jurisdiction ratione personae/materiae/temporis', 'Admissibility and standing', 'Treaty definition and coverage', 'Waiver and fork-in-the-road', 'Viability of claims']],
                'merits_memorial' => ['label' => 'Merits Memorial (Outline)', 'sections' => ['Claimant and investment identification', 'Treaty breach narrative', 'Breach of FET/FPS/NT/MFN', 'Causation and injury evidence', 'Damages and interest calculation']],
                'damages_quantum_note' => ['label' => 'Damages and Quantum Note', 'sections' => ['Valuation date selection', 'DCF vs. comparable transaction analysis', 'Lost profits and mitigation', 'Pre-award and post-award interest', 'Moral damages exposure']],
                'expert_report_brief' => ['label' => 'Expert Report Brief (Economic)', 'sections' => ['Valuation methodology framework', 'Key assumptions and sensitivities', 'Comparable transaction support', 'Alternative scenario testing', 'Conclusions and qualifications']],
                'jurisdictional_objections_response' => ['label' => 'Response to Preliminary Objections', 'sections' => ['Treaty coverage and ratification', 'Claimant standing and nationality', 'Investment definition and timing', 'Waiver and consent analysis', 'Timeliness and admissibility reply']],
                'expropriation_analysis' => ['label' => 'Expropriation and Indirect Taking Analysis', 'sections' => ['Direct expropriation elements', 'Indirect expropriation test (police powers doctrine)', 'Regulatory versus compensable interference', 'Good faith and due process requirement', 'Just compensation baseline']],
                'security_exception_defence' => ['label' => 'Security Exception and Police Powers Defence', 'sections' => ['Emergency/necessity exception text', 'Regulatory autonomy limitation', 'Proportionality and alternative means', 'Genuine security concern evidence', 'Post-award enforcement implications']],
                'arbitration_seat_strategy' => ['label' => 'Seat and Procedural Strategy Note', 'sections' => ['Seat selection (ICSID/UNCITRAL/ICC)', 'Applicable procedural rules', 'Language and tribunal composition', 'Timeline and cost considerations', 'Seat law enforcement landscape']],
                'hearing_brief' => ['label' => 'Hearing Brief and Skeleton Argument', 'sections' => ['Core legal submissions', 'Key factual disputes', 'Document priorities', 'Witness and expert testimony map', 'Closing relief and interest calculations']],
                'award_enforcement_note' => ['label' => 'Award Enforcement and Annulment Strategy', 'sections' => ['Award recognition timeline', 'ICSID/Convention enforcement framework', 'Annulment grounds and risk', 'Respondent asset location', 'Alternative collection strategy']],
            ],
        ],
    ];
}

function aep_enrich_domain_profiles(array $profiles): array
{
    $extensions = aep_domain_library_extensions();
    foreach ($profiles as $key => $profile) {
        $profiles[$key]['subdomains'] = $extensions[$key]['subdomains'] ?? [];
        $profiles[$key]['templates'] = $extensions[$key]['templates'] ?? [];
    }
    return $profiles;
}

function aep_supported_domain(string $domain): ?array
{
    $profiles = aep_domain_profiles();
    return $profiles[$domain] ?? null;
}

function aep_domain_subdomains(string $domain): array
{
    $profile = aep_supported_domain($domain);
    return $profile['subdomains'] ?? [];
}

function aep_domain_templates(string $domain): array
{
    $profile = aep_supported_domain($domain);
    return $profile['templates'] ?? [];
}

function aep_engine_stack_labels(): array
{
    return [
        'outcome_v2' => 'Outcome Engine V2',
        'legal_violation' => 'Legal Violation Engine',
        'clause_obligation' => 'Clause and Obligation Engine',
        'issue_spotting' => 'Issue Spotting Engine',
        'evidence_matrix' => 'Evidence Matrix Engine',
        'counterargument' => 'Counterargument Engine',
        'remedy' => 'Remedy Engine',
        'chronology' => 'Chronology Engine',
        'authority_citation' => 'Authority / Citation Engine',
        'fact_to_issues' => 'Fact-to-Issues Mapper',
        'procedure_limitation' => 'Procedure / Limitation Engine',
        'pleading_generator' => 'Pleading Generator Engine',
        'relief_drafting' => 'Relief Drafting Engine',
        'domain_strategy' => 'Domain Strategy Engine',
        'skeleton_argument' => 'Skeleton Argument Engine',
        'letter_before_action' => 'Letter Before Action Engine',
        'bundle_disclosure' => 'Bundle / Disclosure Engine',
    ];
}

function aep_domain_essentials(array $profile): array
{
    $essentials = [];

    foreach (($profile['required_fields'] ?? []) as $field) {
        $essentials[] = aep_labelize($field);
    }
    foreach (array_slice(($profile['issue_fields'] ?? []), 0, 4) as $field) {
        $essentials[] = aep_labelize($field);
    }

    $blueprint = aep_domain_engine_blueprint((string)($profile['label'] ?? ''));
    foreach (array_slice(($blueprint['elements'] ?? []), 0, 4) as $element) {
        $essentials[] = $element;
    }

    return array_values(array_unique(array_filter($essentials, static fn($v) => trim((string)$v) !== '')));
}

function aep_domain_map(): array
{
    $map = [];
    foreach (aep_domain_profiles() as $key => $profile) {
        $templateLabels = [];
        foreach (($profile['templates'] ?? []) as $templateKey => $template) {
            $templateLabels[] = (string)($template['label'] ?? aep_labelize((string)$templateKey));
        }

        $map[$key] = [
            'key' => $key,
            'label' => (string)($profile['label'] ?? aep_labelize($key)),
            'table' => (string)($profile['table'] ?? ''),
            'subdomains' => array_values($profile['subdomains'] ?? []),
            'subdomain_links' => aep_domain_subdomain_links($key),
            'required_fields' => array_map('aep_labelize', $profile['required_fields'] ?? []),
            'issue_fields' => array_map('aep_labelize', $profile['issue_fields'] ?? []),
            'essentials' => aep_domain_essentials($profile),
            'template_labels' => $templateLabels,
            'engine_stack' => array_values(aep_engine_stack_labels()),
        ];
    }

    return $map;
}

function aep_jurisdiction_options(): array
{
    return [
        'england_wales' => 'England and Wales',
        'scotland' => 'Scotland',
        'northern_ireland' => 'Northern Ireland',
    ];
}

function aep_track_options(): array
{
    return [
        'general' => 'General',
        'pre_action' => 'Pre-Action',
        'small_claims' => 'Small Claims Track',
        'fast_track' => 'Fast Track',
        'multi_track' => 'Multi Track',
        'appeal' => 'Appeal',
        'tribunal' => 'Tribunal',
    ];
}

function aep_reasoning_depth_options(): array
{
    return [
        'standard' => 'Standard',
        'advanced' => 'Advanced',
        'aggressive' => 'Aggressive',
    ];
}

function aep_phrase_bank_categories(): array
{
    return [
        'pleadings' => 'Pleadings',
        'adr_settlement' => 'ADR and Settlement',
        'evidence' => 'Evidence',
        'relief_remedies' => 'Relief and Remedies',
        'tribunal' => 'Tribunal',
        'family' => 'Family',
        'immigration' => 'Immigration',
        'criminal' => 'Criminal',
    ];
}

function aep_phrase_bank_entries(): array
{
    return [
        'pleadings' => [
            'The Claimant avers that the Defendant was in breach of the obligations pleaded herein.',
            'Save as expressly admitted, each allegation not admitted is denied.',
            'Further and in the alternative, the Claimant relies on the matters set out below.',
            'The Defendant is put to strict proof of each allegation not expressly admitted.',
            'For the avoidance of doubt, the pleaded chronology is relied on in full.',
        ],
        'adr_settlement' => [
            'The Claimant remains open to without prejudice discussions aimed at proportionate resolution.',
            'Any settlement proposal is made on a without prejudice save as to costs basis.',
            'The parties are invited to engage in ADR at the earliest appropriate stage.',
            'The Claimant reserves all rights in respect of costs should ADR be unreasonably refused.',
            'Settlement terms should include payment timetable, release wording, and enforcement default terms.',
        ],
        'evidence' => [
            'The documentary record should be read as a whole and in chronological sequence.',
            'Contemporaneous records are relied upon as the best evidence of events.',
            'Any challenge to authenticity is denied and the producing party is put to proof.',
            'The evidence matrix links each pleaded issue to primary and secondary documents.',
            'Witness evidence is expected to address factual events rather than legal conclusion.',
        ],
        'relief_remedies' => [
            'The Claimant seeks damages, interest, costs, and such further relief as the Court deems just.',
            'Relief is pleaded in the alternative where legal and factual contingencies arise.',
            'Interest is claimed pursuant to section 69 County Courts Act 1984 at such rate as the Court thinks fit.',
            'The remedy sought is proportionate to the breach and loss established by evidence.',
            'The Court is invited to grant declaratory and consequential relief where appropriate.',
        ],
        'tribunal' => [
            'The claimant requests case management directions proportionate to the issues and value.',
            'The issues list should be agreed and narrowed in advance of final hearing.',
            'The bundle should be indexed, paginated, and limited to documents in issue.',
            'The party relies on statutory tests as framed in the pleaded causes of action.',
            'A focused witness timetable is sought to preserve hearing efficiency.',
        ],
        'family' => [
            'The welfare of the child is the court’s paramount consideration.',
            'Interim arrangements are sought pending full determination of disputed issues.',
            'Safeguarding concerns are identified and should be addressed by proportionate directions.',
            'The applicant seeks orders that are practical, child-focused, and enforceable.',
            'The court is invited to adopt the least disruptive arrangement consistent with welfare.',
        ],
        'immigration' => [
            'The decision under challenge is unlawful for the reasons set out in the grounds.',
            'The appellant relies on Article 8 proportionality and relevant statutory factors.',
            'The chronology and evidence index should be read alongside refusal reasons.',
            'Any credibility concerns are answered by documentary and contextual evidence.',
            'Relief is sought in the form of remittal, substitution, or other lawful outcome.',
        ],
        'criminal' => [
            'The prosecution is put to strict proof of each element of the alleged offence.',
            'The defence case is that the evidence does not satisfy the criminal standard.',
            'Any confession or statement is challenged on voluntariness and fairness grounds where applicable.',
            'Bail conditions proposed are sufficient to meet identified risks.',
            'In sentencing, the court is invited to consider personal mitigation and proportionality.',
        ],
    ];
}

function aep_phrase_bank_for_category(string $category): array
{
    $all = aep_phrase_bank_entries();
    return $all[$category] ?? [];
}

function aep_normalize_phrase_category(string $category, string $domain = ''): string
{
    $category = trim(strtolower($category));
    $aliases = [
        'statements_of_case' => 'pleadings',
        'without_prejudice_settlement' => 'adr_settlement',
        'adr' => 'adr_settlement',
        'remedies' => 'relief_remedies',
        'relief' => 'relief_remedies',
    ];
    if (isset($aliases[$category])) {
        return $aliases[$category];
    }
    $categories = aep_phrase_bank_categories();
    if (isset($categories[$category])) {
        return $category;
    }
    return aep_phrase_bank_suggested_category($domain);
}

function aep_phrase_bank_suggested_category(string $domain): string
{
    return match ($domain) {
        'family' => 'family',
        'immigration' => 'immigration',
        'criminal' => 'criminal',
        'employment' => 'tribunal',
        default => 'pleadings',
    };
}

function aep_latin_pronunciation_rules(): array
{
    return [
        'ae -> eye (e.g., prae = preye)',
        'oe -> oy (e.g., foedus = foy-dus)',
        'c before e/i/y -> s, otherwise c -> k',
        'g before e/i/y -> j, otherwise g stays hard',
        'qu -> kw',
        'ph -> f',
        'th -> t',
        'x -> ks',
        'Syllable split: usually keep one consonant with next vowel (al-te-ram), but split hard clusters like ct/gn/pt (ac-ta, ag-nus, scrip-tum)',
    ];
}

function aep_latin_pronounce_word(string $word): string
{
    $w = strtolower($word);
    $w = str_replace(['qu', 'ph', 'th', 'x'], ['kw', 'f', 't', 'ks'], $w);
    $w = str_replace(['ae', 'oe'], ['eye', 'oy'], $w);
    $w = preg_replace('/c(?=[eiy])/', 's', $w) ?? $w;
    $w = str_replace('c', 'k', $w);
    $w = preg_replace('/g(?=[eiy])/', 'j', $w) ?? $w;
    return $w;
}

function aep_latin_phonetic(string $text): string
{
    $tokens = preg_split('/([A-Za-z]+)/', $text, -1, PREG_SPLIT_DELIM_CAPTURE);
    if (!is_array($tokens)) {
        return '';
    }

    $out = '';
    foreach ($tokens as $token) {
        if ($token === '') {
            continue;
        }
        if (preg_match('/^[A-Za-z]+$/', $token)) {
            $out .= aep_latin_pronounce_word($token);
        } else {
            $out .= $token;
        }
    }
    return trim($out);
}

function aep_latin_is_vowel(string $ch): bool
{
    return in_array(strtolower($ch), ['a', 'e', 'i', 'o', 'u', 'y'], true);
}

function aep_latin_is_diphthong(string $pair): bool
{
    return in_array(strtolower($pair), ['ae', 'au', 'oe', 'ei', 'eu', 'ui'], true);
}

function aep_latin_onset_clusters(): array
{
    return [
        'bl', 'br', 'cl', 'cr', 'dr', 'fl', 'fr', 'gl', 'gr', 'pl', 'pr', 'tr',
        'sc', 'sk', 'sm', 'sn', 'sp', 'st', 'sw', 'qu', 'ch', 'ph', 'th', 'rh',
        'scr', 'spl', 'spr', 'squ', 'str',
    ];
}

function aep_latin_boundary_offset(string $cluster): int
{
    $cluster = strtolower($cluster);
    $len = strlen($cluster);
    if ($len <= 1) {
        return 0;
    }

    $onsets = aep_latin_onset_clusters();
    if ($len >= 3) {
        $last3 = substr($cluster, -3);
        if (in_array($last3, $onsets, true)) {
            return $len - 3;
        }
    }

    $last2 = substr($cluster, -2);
    if (in_array($last2, $onsets, true)) {
        return $len - 2;
    }

    return $len - 1;
}

function aep_latin_syllable_split_word(string $word): string
{
    $w = strtolower($word);
    $len = strlen($w);
    if ($len <= 3 || !preg_match('/[aeiouy]/', $w)) {
        return $w;
    }

    $syllables = [];
    $start = 0;
    $i = 0;

    while ($i < $len) {
        $vowelPos = -1;
        for ($j = $i; $j < $len; $j++) {
            if (aep_latin_is_vowel($w[$j])) {
                $vowelPos = $j;
                break;
            }
        }
        if ($vowelPos < 0) {
            break;
        }

        $nucleusEnd = $vowelPos;
        if ($vowelPos + 1 < $len && aep_latin_is_vowel($w[$vowelPos + 1])) {
            $pair = substr($w, $vowelPos, 2);
            if (aep_latin_is_diphthong($pair)) {
                $nucleusEnd = $vowelPos + 1;
            }
        }

        $nextVowel = -1;
        for ($j = $nucleusEnd + 1; $j < $len; $j++) {
            if (aep_latin_is_vowel($w[$j])) {
                $nextVowel = $j;
                break;
            }
        }
        if ($nextVowel < 0) {
            break;
        }

        $cluster = substr($w, $nucleusEnd + 1, $nextVowel - ($nucleusEnd + 1));
        $offset = aep_latin_boundary_offset($cluster);
        $boundary = ($nucleusEnd + 1) + $offset;
        if ($boundary <= $start || $boundary >= $len) {
            break;
        }

        $syllables[] = substr($w, $start, $boundary - $start);
        $start = $boundary;
        $i = $start;
    }

    $tail = substr($w, $start);
    if ($tail !== '') {
        $syllables[] = $tail;
    }

    return implode('-', array_values(array_filter($syllables, static fn($s) => $s !== '')));
}

function aep_latin_syllable_split(string $text): string
{
    $tokens = preg_split('/([A-Za-z]+)/', $text, -1, PREG_SPLIT_DELIM_CAPTURE);
    if (!is_array($tokens)) {
        return '';
    }

    $out = '';
    foreach ($tokens as $token) {
        if ($token === '') {
            continue;
        }
        if (preg_match('/^[A-Za-z]+$/', $token)) {
            $out .= aep_latin_syllable_split_word($token);
        } else {
            $out .= $token;
        }
    }
    return trim($out);
}

function aep_render_template_text(string $domain, string $templateKey, array $case = [], array $options = []): string
{
    $profile = aep_supported_domain($domain);
    if (!$profile) {
        return '';
    }

    $templates = $profile['templates'] ?? [];
    $template = $templates[$templateKey] ?? null;
    if (!$template) {
        return '';
    }

    $lines = [];
    $jurisdictions = aep_jurisdiction_options();
    $tracks = aep_track_options();
    $jurisdiction = (string)($options['jurisdiction'] ?? 'england_wales');
    if (!isset($jurisdictions[$jurisdiction])) {
        $jurisdiction = 'england_wales';
    }
    $track = (string)($options['track'] ?? 'general');
    if (!isset($tracks[$track])) {
        $track = 'general';
    }
    $focus = trim((string)($options['focus'] ?? ''));
    $docType = (string)($options['doc_type'] ?? '');
    $reasoningDepth = (string)($options['reasoning_depth'] ?? 'standard');
    $reasoningOptions = aep_reasoning_depth_options();
    if (!isset($reasoningOptions[$reasoningDepth])) {
        $reasoningDepth = 'standard';
    }
    $phraseCategories = aep_phrase_bank_categories();
    $phraseCategory = aep_normalize_phrase_category((string)($options['phrase_category'] ?? ''), $domain);
    $instructions = trim((string)($options['instructions'] ?? ''));

    $lines[] = 'Domain: ' . $profile['label'];
    $lines[] = 'Template: ' . ($template['label'] ?? $templateKey);
    $lines[] = 'Jurisdiction: ' . $jurisdictions[$jurisdiction];
    $lines[] = 'Procedure track: ' . $tracks[$track];
    $lines[] = 'Reasoning depth: ' . $reasoningOptions[$reasoningDepth];
    $lines[] = 'Phrase pack: ' . $phraseCategories[$phraseCategory];
    if ($focus !== '') {
        $lines[] = 'Subdomain focus: ' . aep_labelize(str_replace('-', ' ', $focus));
    }
    $lines[] = '';

    if ($docType !== '') {
        $docTypeLabel = match ($docType) {
            'analysis_brief' => 'Analysis brief',
            'draft_outline' => 'Draft outline',
            'instructions_note' => 'Instructions note',
            'counsel_note' => 'Counsel note',
            default => aep_labelize($docType),
        };
        $docTypeHints = [
            'analysis_brief' => 'Lead with the bottom line, then the issues, strength, weaknesses, and next move.',
            'draft_outline' => 'Translate the facts into a structured pleading or litigation note.',
            'instructions_note' => 'Capture objectives, facts needed, and the action list without filler.',
            'counsel_note' => 'Use a firm, practical legal tone and state the recommendation plainly.',
        ];
        $lines[] = 'Document focus: ' . $docTypeLabel;
        $lines[] = '- ' . ($docTypeHints[$docType] ?? 'Tailor the draft to the selected document type.');
        $lines[] = '';
    }

    if ($jurisdiction !== 'england_wales') {
        $lines[] = 'Jurisdiction alignment note';
        $lines[] = '- Confirm court and form references against local procedural rules before filing.';
        $lines[] = '- Validate any CPR references and substitute local rule equivalents where required.';
        $lines[] = '';
    }

    if ($track !== 'general') {
        $lines[] = 'Track focus adjustments';
        $trackHints = [
            'pre_action' => 'Prioritize protocol compliance, early disclosure requests, and settlement framing.',
            'small_claims' => 'Keep pleadings concise, evidence practical, and costs proportionate.',
            'fast_track' => 'Tighten witness statements, issues list, and hearing timetable discipline.',
            'multi_track' => 'Structure disclosure strategy, expert scope, and case management directions in detail.',
            'appeal' => 'Focus on appealable error, standard of review, and relief on appeal.',
            'tribunal' => 'Use tribunal-specific forms, issue framing, and remedy headings.',
        ];
        $lines[] = '- ' . ($trackHints[$track] ?? 'Tailor drafting to the selected procedural track.');
        $lines[] = '';
    }

    if (!empty($case)) {
        $summary = aep_case_summary($profile, $case);
        if ($summary !== '') {
            $lines[] = 'Case summary: ' . $summary;
            $lines[] = '';
        }
    }

    if ($instructions !== '') {
        $lines[] = 'Working instructions';
        $lines[] = '- ' . $instructions;
        $lines[] = '';
    }

    $lines[] = 'Sections';
    foreach (($template['sections'] ?? []) as $section) {
        $lines[] = '- ' . $section;
    }
    $lines[] = '';

    $lines[] = 'Standard drafting phrases (' . $phraseCategories[$phraseCategory] . ')';
    foreach (aep_phrase_bank_for_category($phraseCategory) as $phrase) {
        $lines[] = '- ' . $phrase;
    }
    $lines[] = '';

    if ($domain === 'latin_maxims') {
        $maximText = trim((string)($case['maxim'] ?? ''));
        if ($maximText !== '') {
            $lines[] = 'Pronunciation aid';
            $lines[] = '- Maxim: ' . $maximText;
            $lines[] = '- Approx pronunciation: ' . aep_latin_phonetic($maximText);
            $lines[] = '- Syllable split: ' . aep_latin_syllable_split($maximText);
            $lines[] = '';
        }
        $lines[] = 'Pronunciation formula (quick rules)';
        foreach (aep_latin_pronunciation_rules() as $rule) {
            $lines[] = '- ' . $rule;
        }
        $lines[] = '';
    }

    $lines[] = 'Required data checklist';
    foreach (($profile['required_fields'] ?? []) as $field) {
        $lines[] = '- ' . aep_labelize($field);
    }

    return implode("\n", $lines);
}

function aep_load_case(string $domain, int $id): ?array
{
    $profile = aep_supported_domain($domain);
    if (!$profile || $id <= 0) {
        return null;
    }

    $pdo = aep_db();
    if (!aep_table_exists($pdo, $profile['table'])) {
        return null;
    }

    $stmt = $pdo->prepare("SELECT * FROM {$profile['table']} WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $case = $stmt->fetch(PDO::FETCH_ASSOC);
    return $case ?: null;
}

function aep_case_score(array $profile, array $case): array
{
    $found = 0;
    $total = max(1, count($profile['required_fields']));
    $gaps = [];

    foreach ($profile['required_fields'] as $field) {
        if (!empty($case[$field])) {
            $found++;
        } else {
            $gaps[] = $field;
        }
    }

    $pct = (int) round(($found / $total) * 100);
    $rating = $pct >= 80 ? 'Strong' : ($pct >= 50 ? 'Moderate' : 'Weak');

    return [$found, $total, $pct, $rating, $gaps];
}

function aep_labelize(string $field): string
{
    return ucwords(str_replace(['_', '-'], ' ', $field));
}

function aep_slugify(string $value): string
{
    $value = strtolower(trim($value));
    $value = preg_replace('/[^a-z0-9]+/', '-', $value) ?? $value;
    $value = trim($value, '-');
    return $value !== '' ? $value : 'focus';
}

function aep_domain_focus_route_map(): array
{
    return [
        'human_rights' => [
            'civil-liberties' => ['templates' => ['violation_assessment', 'urgent_injunction_note']],
            'police-powers' => ['templates' => ['state_liability_brief', 'human_rights_jr_form_pack']],
            'detention-and-prison-rights' => ['templates' => ['urgent_injunction_note', 'detention_bail_strategy']],
            'discrimination-and-equality' => ['templates' => ['proportionality_analysis', 'state_liability_brief']],
            'privacy-and-surveillance' => ['templates' => ['violation_assessment', 'interim_relief_checklist']],
            'freedom-of-expression-and-assembly' => ['templates' => ['human_rights_permission_appeal', 'human_rights_trial_bundle_plan']],
        ],
        'admin_law' => [
            'judicial-review' => ['templates' => ['jr_ground_matrix', 'admin_law_skeleton', 'admin_jr_claim_form_pack']],
            'regulatory-enforcement' => ['templates' => ['pre_action_protocol', 'permission_stage_note']],
            'procurement-disputes' => ['templates' => ['permission_stage_note', 'admin_costs_protective_order']],
            'public-body-discipline' => ['templates' => ['admin_law_skeleton', 'remedies_schedule']],
            'licensing-and-permits' => ['templates' => ['pre_action_protocol', 'remedies_schedule']],
            'planning-and-local-authority-decisions' => ['templates' => ['jr_ground_matrix', 'admin_n461_statement_pack']],
        ],
        'tort' => [
            'negligence' => ['templates' => ['liability_matrix', 'duty_breach_note']],
            'defamation' => ['templates' => ['defamation_defence_map', 'tort_pre_action_letter']],
            'nuisance' => ['templates' => ['causation_remoteness_brief', 'tort_pre_action_letter']],
            'occupiers-liability' => ['templates' => ['duty_breach_note', 'liability_matrix']],
            'professional-negligence' => ['templates' => ['liability_matrix', 'schedule_of_loss']],
            'product-liability' => ['templates' => ['liability_matrix', 'tort_trial_bundle_index']],
        ],
        'oil_gas' => [
            'farm-in-and-farm-out' => ['templates' => ['operator_dispute_brief', 'arbitration_readiness_pack']],
            'joint-operating-agreements-joa' => ['templates' => ['joint_operating_agreement_note', 'operator_dispute_brief']],
            'risk-allocation-and-indemnities' => ['templates' => ['operator_dispute_brief', 'oilgas_regulatory_response']],
            'mutual-hold-harmless-and-insurance' => ['templates' => ['operator_dispute_brief', 'oilgas_regulatory_response']],
            'licensing-and-regulatory-compliance' => ['templates' => ['regulatory_compliance_review', 'oilgas_regulatory_response']],
            'pipeline-transport-and-decommissioning' => ['templates' => ['environmental_incident_response', 'regulatory_compliance_review']],
        ],
        'commercial_law' => [
            'commercial-contracts' => ['templates' => ['commercial_contract_analysis', 'commercial_dispute_strategy']],
            'mergers-and-acquisitions' => ['templates' => ['m_and_a_due_diligence_checklist', 'commercial_settlement_proposal']],
            'distribution-and-agency' => ['templates' => ['distribution_agreement_review', 'commercial_dispute_strategy']],
            'technology-licensing' => ['templates' => ['technology_licence_summary', 'commercial_contract_analysis']],
            'franchise-agreements' => ['templates' => ['commercial_contract_analysis', 'commercial_settlement_proposal']],
            'warranty-and-indemnity-claims' => ['templates' => ['warranty_indemnity_claim', 'commercial_dispute_strategy']],
        ],
        'contract' => [
            'commercial-contracts' => ['templates' => ['breach_analysis', 'demand_letter']],
            'supply-and-services' => ['templates' => ['breach_analysis', 'contract_directions_questionnaire']],
            'construction-contracts' => ['templates' => ['contract_directions_questionnaire', 'settlement_term_sheet']],
            'settlement-and-release' => ['templates' => ['settlement_term_sheet', 'contract_reply_to_defence']],
            'technology-and-saas-contracts' => ['templates' => ['breach_analysis', 'contract_n9b_defence_pack']],
            'consumer-and-b2b-terms' => ['templates' => ['demand_letter', 'contract_n1_claim_pack']],
        ],
        'property' => [
            'residential-tenancy' => ['templates' => ['notice_validity_check', 'property_n5b_possession_pack']],
            'commercial-lease' => ['templates' => ['lease_breach_schedule', 'property_trial_bundle_plan']],
            'possession-proceedings' => ['templates' => ['possession_case_plan', 'property_n11m_defence_pack']],
            'conveyancing-disputes' => ['templates' => ['title_dispute_strategy', 'property_trial_bundle_plan']],
            'service-charge-and-disrepair' => ['templates' => ['disrepair_evidence_pack', 'property_trial_bundle_plan']],
            'boundary-and-title-disputes' => ['templates' => ['title_dispute_strategy', 'property_trial_bundle_plan']],
        ],
        'criminal' => [
            'summary-offences' => ['templates' => ['defence_theory', 'bail_submission']],
            'indictable-offences' => ['templates' => ['defence_theory', 'cross_examination_plan']],
            'bail-and-custody' => ['templates' => ['bail_submission', 'criminal_bail_variation_form_pack']],
            'sentencing-strategy' => ['templates' => ['sentencing_mitigation_note', 'criminal_case_statement_response']],
            'disclosure-and-admissibility' => ['templates' => ['evidence_admissibility_challenge', 'criminal_case_statement_response']],
            'appeal-against-conviction-or-sentence' => ['templates' => ['criminal_appeal_notice', 'criminal_appeal_form_pack']],
        ],
        'immigration' => [
            'work-and-skilled-worker-routes' => ['templates' => ['eligibility_assessment', 'immigration_bundle_plan']],
            'family-and-article-8-routes' => ['templates' => ['article8_balancing_note', 'eligibility_assessment']],
            'asylum-and-protection' => ['templates' => ['credibility_matrix', 'immigration_bundle_plan']],
            'appeals-and-judicial-review' => ['templates' => ['immigration_jr_grounds', 'immigration_jr_form_pack']],
            'sponsor-licence-and-compliance' => ['templates' => ['eligibility_assessment', 'immigration_bundle_plan']],
            'detention-bail-and-removal-challenges' => ['templates' => ['detention_bail_strategy', 'immigration_jr_form_pack']],
        ],
        'employment' => [
            'unfair-dismissal' => ['templates' => ['tribunal_claim_plan', 'employment_list_of_issues']],
            'discrimination' => ['templates' => ['tribunal_claim_plan', 'schedule_of_loss_employment']],
            'whistleblowing' => ['templates' => ['tribunal_claim_plan', 'settlement_strategy']],
            'wage-and-contract-claims' => ['templates' => ['schedule_of_loss_employment', 'employment_case_management_agenda']],
            'redundancy-and-tupe' => ['templates' => ['employment_case_management_agenda', 'tribunal_claim_plan']],
            'restrictive-covenants' => ['templates' => ['settlement_strategy', 'tribunal_claim_plan']],
        ],
        'family' => [
            'divorce-and-dissolution' => ['templates' => ['financial_relief_outline', 'case_management_position_statement']],
            'child-arrangements' => ['templates' => ['child_welfare_note', 'section7_analysis']],
            'financial-remedies' => ['templates' => ['financial_relief_outline', 'family_case_management_position_statement']],
            'domestic-abuse-protection' => ['templates' => ['non_molestation_application', 'family_fl401_form_pack']],
            'care-proceedings' => ['templates' => ['section7_analysis', 'case_management_position_statement']],
            'international-relocation-and-abduction' => ['templates' => ['family_fact_finding_plan', 'child_welfare_note']],
        ],
        'latin_maxims' => [
            'equity-maxims' => ['templates' => ['maxim_application_note', 'maxim_argument_bank']],
            'procedure-maxims' => ['templates' => ['maxim_to_issue_matrix', 'maxim_caution_note']],
            'evidence-maxims' => ['templates' => ['maxim_authority_table', 'maxim_rebuttal_sheet']],
            'remedy-maxims' => ['templates' => ['maxim_application_note', 'maxim_oral_delivery_card']],
            'interpretation-maxims' => ['templates' => ['maxim_argument_bank', 'maxim_authorities_bundle_pack']],
            'public-law-maxims' => ['templates' => ['maxim_judicial_notice_note', 'maxim_pronunciation_sheet']],
        ],
        'international_arbitration' => [
            'investor-state-disputes-isds' => ['templates' => ['treaty_breach_analysis', 'preliminary_objections_brief']],
            'treaty-arbitration' => ['templates' => ['treaty_breach_analysis', 'jurisdictional_objections_response']],
            'commercial-arbitration' => ['templates' => ['arbitration_seat_strategy', 'hearing_brief']],
            'preliminary-objections' => ['templates' => ['preliminary_objections_brief', 'jurisdictional_objections_response']],
            'merits-phase' => ['templates' => ['merits_memorial', 'damages_quantum_note']],
            'post-award-enforcement' => ['templates' => ['award_enforcement_note', 'expert_report_brief']],
        ],
    ];
}

function aep_domain_subdomain_links(string $domain, array $context = []): array
{
    $profile = aep_supported_domain($domain);
    if (!$profile) {
        return [];
    }

    $templateKeys = array_keys($profile['templates'] ?? []);
    $routeMap = aep_domain_focus_route_map();
    $defaults = [
        'doc_type' => (string)($context['doc_type'] ?? 'draft_outline'),
        'jurisdiction' => (string)($context['jurisdiction'] ?? 'england_wales'),
        'track' => (string)($context['track'] ?? 'general'),
        'reasoning_depth' => (string)($context['reasoning_depth'] ?? 'standard'),
        'phrase_category' => (string)($context['phrase_category'] ?? ''),
    ];

    $links = [];
    foreach (($profile['subdomains'] ?? []) as $subdomain) {
        $slug = aep_slugify($subdomain);
        $suggestions = $routeMap[$domain][$slug]['templates'] ?? [];
        if (empty($suggestions) && !empty($templateKeys)) {
            $suggestions = array_slice($templateKeys, 0, 2);
        }
        $suggestions = array_values(array_filter(
            $suggestions,
            static fn(string $templateKey): bool => isset($profile['templates'][$templateKey])
        ));
        if (empty($suggestions) && !empty($templateKeys)) {
            $suggestions = array_slice($templateKeys, 0, 2);
        }
        $primaryTemplate = $suggestions[0] ?? '';

        $workbenchQuery = array_merge($defaults, [
            'domain' => $domain,
            'focus' => $slug,
            'focus_label' => $subdomain,
        ]);
        if ($primaryTemplate !== '') {
            $workbenchQuery['template'] = $primaryTemplate;
        }
        $workbenchHref = 'domain_workbench.php?' . http_build_query($workbenchQuery);

        $templateLinks = [];
        foreach ($suggestions as $templateKey) {
            if (!isset($profile['templates'][$templateKey])) {
                continue;
            }
            $templateQuery = array_merge($workbenchQuery, ['template' => $templateKey]);
            $templateLinks[] = [
                'key' => $templateKey,
                'label' => (string)($profile['templates'][$templateKey]['label'] ?? $templateKey),
                'href' => 'counsel_engine.php?' . http_build_query($templateQuery),
            ];
        }

        $links[] = [
            'label' => $subdomain,
            'slug' => $slug,
            'focus_label' => $subdomain,
            'workbench_href' => $workbenchHref,
            'template_links' => $templateLinks,
        ];
    }

    return $links;
}

function aep_case_summary(array $profile, array $case): string
{
    $parts = [];
    foreach ($profile['summary_fields'] as $field) {
        if (!empty($case[$field])) {
            $parts[] = aep_labelize($field) . ': ' . $case[$field];
        }
    }
    return implode(' | ', $parts);
}

function aep_case_field_present(array $case, string $field): bool
{
    if (!array_key_exists($field, $case)) {
        return false;
    }

    $value = $case[$field];
    if (is_string($value)) {
        return trim($value) !== '';
    }
    if (is_numeric($value)) {
        return true;
    }
    return !empty($value);
}

function aep_has_material_content(mixed $value): bool
{
    if (is_array($value)) {
        if (empty($value)) {
            return false;
        }
        foreach ($value as $item) {
            if (aep_has_material_content($item)) {
                return true;
            }
        }
        return false;
    }

    if (is_string($value)) {
        return trim($value) !== '';
    }

    if (is_numeric($value)) {
        return true;
    }

    return !empty($value);
}

function aep_outcome_engine_v2(array $profile, array $case, int $requiredFound, int $requiredTotal, int $requiredPct): array
{
    $issueFields = $profile['issue_fields'] ?? [];
    $issueTotal = max(1, count($issueFields));
    $issueFound = 0;
    foreach ($issueFields as $field) {
        if (aep_case_field_present($case, $field)) {
            $issueFound++;
        }
    }
    $issuePct = (int) round(($issueFound / $issueTotal) * 100);

    $proceduralCandidates = [
        'case_reference', 'court_name', 'court_date', 'hearing_date', 'notice_date',
        'decision_date', 'appeal_date', 'contract_date', 'offence_date', 'violation_date',
    ];
    $proceduralTotal = 0;
    $proceduralFound = 0;
    foreach ($proceduralCandidates as $field) {
        if (array_key_exists($field, $case)) {
            $proceduralTotal++;
            if (aep_case_field_present($case, $field)) {
                $proceduralFound++;
            }
        }
    }
    $proceduralPct = $proceduralTotal > 0 ? (int) round(($proceduralFound / $proceduralTotal) * 100) : 50;

    $riskFromGaps = (int) round((1 - ($requiredFound / max(1, $requiredTotal))) * 100);
    $status = strtolower(trim((string)($case['status'] ?? '')));
    $statusRisk = match ($status) {
        'lost', 'withdrawn' => 25,
        'closed' => 20,
        'draft' => 10,
        default => 0,
    };
    $riskScore = max(0, min(100, (int) round(($riskFromGaps * 0.7) + ($statusRisk * 0.3))));
    $outcomeIndex = max(0, min(100, (int) round(($requiredPct * 0.55) + ($issuePct * 0.30) + ($proceduralPct * 0.15) - ($riskScore * 0.20))));

    $confidenceRaw = (int) round(($requiredPct * 0.65) + ($issuePct * 0.35));
    $confidenceBand = $confidenceRaw >= 75 ? 'High' : ($confidenceRaw >= 50 ? 'Medium' : 'Low');

    $likelyOutcome = $outcomeIndex >= 70
        ? 'Favorable trajectory if evidence and procedure remain consistent.'
        : ($outcomeIndex >= 45
            ? 'Contested trajectory with mixed prospects; targeted strengthening required.'
            : 'Adverse trajectory unless core evidential and procedural gaps are fixed quickly.');

    $bestCase = $outcomeIndex >= 60
        ? 'Best case: strong merits presentation with favorable relief or settlement terms.'
        : 'Best case: narrowed issues and negotiated settlement after evidence consolidation.';
    $worstCase = $outcomeIndex >= 60
        ? 'Worst case: partial success with reduced remedies due to unresolved risk points.'
        : 'Worst case: dismissal, weak relief, or costs exposure if current weaknesses persist.';

    $recommendations = [];
    if ($confidenceBand === 'Low') {
        $recommendations[] = 'Prioritize documentary completeness before escalating strategy.';
    }
    if ($riskScore >= 60) {
        $recommendations[] = 'Adopt risk-control strategy: preserve settlement optionality and tighten pleadable facts.';
    }
    if ($proceduralPct < 60) {
        $recommendations[] = 'Fix procedural readiness: confirm dates, forum references, and service/timeline compliance.';
    }
    if ($outcomeIndex >= 70 && $confidenceBand !== 'Low') {
        $recommendations[] = 'Proceed assertively with merits-led submissions and remedy-focused drafting.';
    }
    if (empty($recommendations)) {
        $recommendations[] = 'Maintain current strategy and monitor new evidence for scenario shift.';
    }

    return [
        'outcome_index' => $outcomeIndex,
        'risk_score' => $riskScore,
        'confidence_score' => $confidenceRaw,
        'confidence_band' => $confidenceBand,
        'evidence_coverage' => $issuePct,
        'procedural_readiness' => $proceduralPct,
        'scenario_best' => $bestCase,
        'scenario_likely' => 'Likely case: ' . $likelyOutcome,
        'scenario_worst' => $worstCase,
        'recommendations' => $recommendations,
    ];
}

function aep_case_text_blob(array $case, array $fields = []): string
{
    $parts = [];
    $keys = $fields ?: array_keys($case);
    foreach ($keys as $field) {
        if (!array_key_exists($field, $case)) {
            continue;
        }
        $value = $case[$field];
        if (is_string($value)) {
            $trimmed = trim($value);
            if ($trimmed !== '') {
                $parts[] = $trimmed;
            }
        } elseif (is_numeric($value)) {
            $parts[] = (string) $value;
        }
    }
    return trim(implode(' ', $parts));
}

function aep_case_first_non_empty(array $case, array $fields): string
{
    foreach ($fields as $field) {
        if (!array_key_exists($field, $case)) {
            continue;
        }
        $value = $case[$field];
        if (!is_string($value) && !is_numeric($value)) {
            continue;
        }
        $text = trim((string) $value);
        if ($text !== '') {
            return $text;
        }
    }
    return '';
}

function aep_engine_find_terms(string $text, array $terms): array
{
    $found = [];
    $haystack = strtolower($text);
    foreach ($terms as $term => $label) {
        $patterns = is_array($term) ? $term : [$term];
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $haystack) === 1) {
                $found[] = $label;
                break;
            }
        }
    }
    return array_values(array_unique($found));
}

function aep_legal_violation_engine(array $profile, array $case): array
{
    $domain = $profile['label'] ?? 'Unknown';
    $blob = aep_case_text_blob($case, array_merge($profile['summary_fields'] ?? [], $profile['issue_fields'] ?? [], ['summary', 'grounds', 'dispute_summary', 'dispute_description', 'contract_description', 'legal_basis', 'breach_description']));
    $violations = [];

    $humanRightsMap = [
        '/discrimin/i' => 'Equality / non-discrimination',
        '/detain|arrest|custod/i' => 'Liberty and security',
        '/censor|expression|speech|broadcast|press/i' => 'Freedom of expression',
        '/privacy|surveil|search/i' => 'Privacy',
        '/fair hearing|fair trial|counsel|lawyer/i' => 'Fair hearing',
        '/religion|belief/i' => 'Freedom of religion or belief',
        '/assembly|association|protest/i' => 'Freedom of assembly and association',
    ];
    $domainMap = [
        'Administrative Law' => [
            '/irrational|unfair|procedur|bias|failure to consider|fetter/i' => 'Public law unlawfulness / procedural unfairness',
        ],
        'Tort Law' => [
            '/neglig|breach|injur|harm|damage/i' => 'Duty / breach / causation / loss',
        ],
        'Commercial Law' => [
            '/breach|termination|payment|default|force majeure|indemn/i' => 'Contractual breach / performance failure',
        ],
        'Contract Law' => [
            '/breach|repudiat|non[- ]payment|termination|notice|condition precedent/i' => 'Contract breach / repudiation / non-performance',
        ],
        'Property and Landlord Tenant' => [
            '/possession|evict|arrears|tenan|lease|notice|repair|deposit/i' => 'Tenancy / possession / notice dispute',
        ],
        'Criminal Law' => [
            '/charge|offence|bail|disclosure|confession|identification|search|seizure/i' => 'Criminal liability / fair process issue',
        ],
        'Immigration Law' => [
            '/visa|leave|asylum|deport|remov|refusal|article 8|credibil/i' => 'Immigration refusal / removal / rights issue',
        ],
        'Employment Law' => [
            '/dismiss|redundan|disciplin|grievance|discrimin|whistle|wage/i' => 'Dismissal / discrimination / workplace breach',
        ],
        'Family Law' => [
            '/child|contact|residence|custody|maintenance|welfare|domestic abuse/i' => 'Child welfare / family relief issue',
        ],
        'Oil and Gas' => [
            '/licen|permit|environment|compliance|royalt|operator/i' => 'Regulatory or contractual non-compliance',
        ],
        'Latin Maxims' => [
            '/equity|justice|construction|interpret|strict proof|burden/i' => 'Maxim-linked principle requiring legal application',
        ],
        'International Investment & Arbitration' => [
            '/expropriat|fair and equitable|FET|FPS|MFN|national treatment|jurisdiction|consent/i' => 'Treaty breach / jurisdictional violation',
        ],
    ];

    $maps = $domain === 'Human Rights' ? [$humanRightsMap] : [];
    if (isset($domainMap[$domain])) {
        $maps[] = $domainMap[$domain];
    }
    if (empty($maps)) {
        $maps[] = [
            '/breach|unlawful|wrongful|failure|violation/i' => 'Potential legal breach',
        ];
    }

    foreach ($maps as $map) {
        foreach ($map as $pattern => $label) {
            if (preg_match($pattern, $blob) === 1) {
                $violations[] = [
                    'label' => $label,
                    'rule' => aep_case_first_non_empty($case, ['article_section', 'legal_basis', 'ground_of_review', 'charges', 'treaty_basis']),
                    'conduct' => aep_case_first_non_empty($case, ['grounds', 'dispute_summary', 'breach_description', 'decision_description', 'dispute_description', 'summary']),
                    'proof' => aep_case_first_non_empty($case, ['summary', 'dispute_description', 'contract_description', 'transaction_description', 'injury_description', 'investment_description']),
                    'remedy_hint' => aep_case_first_non_empty($case, ['remedy', 'relief_sought', 'damages_claimed', 'value_at_risk', 'dispute_amount']),
                ];
            }
        }
    }

    if (empty($violations) && !empty($blob)) {
        $violations[] = [
            'label' => 'Issue requiring legal review',
            'rule' => trim((string)($case['legal_basis'] ?? $case['article_section'] ?? $case['ground_of_review'] ?? '')),
            'conduct' => trim((string)($case['grounds'] ?? $case['summary'] ?? $case['dispute_summary'] ?? '')),
            'proof' => trim((string)($case['summary'] ?? '')),
            'remedy_hint' => trim((string)($case['remedy'] ?? $case['relief_sought'] ?? '')),
        ];
    }

    return $violations;
}

function aep_clause_obligation_engine(array $profile, array $case): array
{
    $blob = aep_case_text_blob($case, ['contract_description', 'transaction_description', 'dispute_summary', 'dispute_description', 'legal_basis', 'without_prejudice', 'summary']);
    $sentences = preg_split('/(?<=[\.\!\?])\s+/', $blob) ?: [];
    $hits = [];
    $clauseTerms = [
        '/\bmust\b|\bshall\b|\brequired to\b|\bobliged to\b/i' => 'Mandatory obligation',
        '/\bwithin\b|\bby\b|\bno later than\b|\bdeadline\b/i' => 'Time obligation',
        '/\bnotice\b|\bservice\b|\bdelivery\b/i' => 'Notice/service clause',
        '/\bterminate\b|\btermination\b|\brepudiate\b/i' => 'Termination right',
        '/\bindemn/i' => 'Indemnity / risk allocation',
        '/\bforce majeure\b/i' => 'Force majeure / excuse',
        '/\bgoverning law\b|\bjurisdiction\b|\barbitration\b/i' => 'Forum / governing law clause',
        '/\bpayment\b|\bfee\b|\bprice\b|\bconsideration\b/i' => 'Payment obligation',
        '/\bconfidential|\bwithout prejudice\b/i' => 'Confidentiality / settlement clause',
    ];

    foreach ($sentences as $sentence) {
        $sentence = trim((string)$sentence);
        if ($sentence === '') {
            continue;
        }
        foreach ($clauseTerms as $pattern => $label) {
            if (preg_match($pattern, $sentence) === 1) {
                $hits[] = [
                    'clause' => $label,
                    'text' => $sentence,
                    'impact' => str_contains(strtolower($sentence), 'breach') ? 'Possible breach point' : 'Relevant contractual or procedural term',
                ];
                break;
            }
        }
    }

    if (empty($hits)) {
        $hits[] = [
            'clause' => 'No express clause extracted',
            'text' => trim((string)($case['contract_description'] ?? $case['summary'] ?? '')),
            'impact' => 'The file needs clearer operative terms, deadlines, and default/remedy language.',
        ];
    }

    return $hits;
}

function aep_issue_spotting_engine(array $profile, array $case): array
{
    $issues = [];
    $profileIssues = $profile['issue_fields'] ?? [];
    foreach ($profileIssues as $field) {
        if (aep_case_field_present($case, $field)) {
            $issues[] = [
                'issue' => aep_labelize($field),
                'why_it_matters' => 'This field contains pleaded facts that drive liability, defence, or relief.',
            ];
        } else {
            $issues[] = [
                'issue' => aep_labelize($field),
                'why_it_matters' => 'This point is not yet sufficiently pleaded or documented.',
            ];
        }
    }

    $genericIssues = [
        'Limitation / time bar',
        'Jurisdiction / forum',
        'Standing / capacity',
        'Proof / evidence sufficiency',
        'Remedy / relief fit',
    ];
    foreach ($genericIssues as $item) {
        $issues[] = [
            'issue' => $item,
            'why_it_matters' => 'Check whether the present facts and documents support this element before drafting final advice.',
        ];
    }

    return $issues;
}

function aep_evidence_matrix_engine(array $profile, array $case): array
{
    $blob = strtolower(aep_case_text_blob($case));
    $rows = [];
    $sources = [
        'Decision / notice / letter' => ['decision', 'notice', 'letter', 'refusal', 'determination'],
        'Chronology' => ['date', 'timeline', 'chronology', 'incident', 'violation', 'dispute'],
        'Contract / policy / governing instrument' => ['contract', 'agreement', 'policy', 'lease', 'constitution', 'treaty', 'article'],
        'Witness evidence' => ['witness', 'statement', 'testimony', 'affidavit'],
        'Expert / technical evidence' => ['expert', 'medical', 'forensic', 'technical', 'valuation'],
        'Correspondence' => ['email', 'correspondence', 'letter', 'notice', 'demand'],
    ];

    foreach ($sources as $label => $terms) {
        $present = false;
        foreach ($terms as $term) {
            if (str_contains($blob, strtolower($term))) {
                $present = true;
                break;
            }
        }
        $rows[] = [
            'source' => $label,
            'status' => $present ? 'present' : 'missing',
            'importance' => $present ? 'Supports the current record' : 'Should be obtained if the issue remains live',
        ];
    }

    return $rows;
}

function aep_domain_engine_blueprint(string $label): array
{
    return match ($label) {
        'Human Rights' => [
            'case_theory' => 'State interference with a protected right, without lawful and proportionate justification.',
            'elements' => ['Right engaged', 'Interference by public authority', 'Lack of lawful/proportionate justification', 'Loss, urgency, and remedy'],
            'proof_points' => ['Decision or detention record', 'Chronology and correspondence', 'Medical or witness evidence', 'Public authority nexus'],
            'pressure_points' => ['Promptness and exhaustion', 'Proportionality', 'Urgent interim relief', 'Causation and practical remedy'],
            'defence_points' => ['Lawful basis', 'Necessity/proportionality', 'No public authority act', 'No causative loss'],
            'pre_action_days' => 7,
        ],
        'Administrative Law' => [
            'case_theory' => 'Public decision infected by illegality, irrationality, procedural unfairness, or disproportionate interference.',
            'elements' => ['Amenable public decision', 'Ground of review', 'Materiality', 'Prompt and proper remedy route'],
            'proof_points' => ['Decision and reasons', 'Policy or statutory framework', 'Fairness record', 'Chronology of challenge'],
            'pressure_points' => ['Permission threshold', 'Promptness', 'Alternative remedy', 'Urgency'],
            'defence_points' => ['Discretion lawfully exercised', 'No material unfairness', 'Alternative remedy', 'Delay'],
            'pre_action_days' => 7,
        ],
        'Tort Law' => [
            'case_theory' => 'Duty owed, breached, and causative of recoverable loss.',
            'elements' => ['Duty', 'Breach', 'Causation', 'Loss and remoteness'],
            'proof_points' => ['Incident record', 'Technical or medical evidence', 'Witness account', 'Schedule of loss'],
            'pressure_points' => ['Causation', 'Expert support', 'Mitigation', 'Quantum discipline'],
            'defence_points' => ['No duty', 'No breach', 'Contributory negligence', 'Loss too remote'],
            'pre_action_days' => 14,
        ],
        'Commercial Law' => [
            'case_theory' => 'Commercial promise or risk allocation breached, causing measurable exposure requiring urgent protective leverage.',
            'elements' => ['Transaction structure', 'Operative terms', 'Breach/default event', 'Commercial loss and remedy'],
            'proof_points' => ['Contract stack', 'Correspondence and notices', 'Payment or performance record', 'Quantum exposure summary'],
            'pressure_points' => ['Termination rights', 'Limitation/exclusion clauses', 'Service mechanics', 'Settlement leverage'],
            'defence_points' => ['No breach', 'Risk allocated by contract', 'Condition precedent unmet', 'Loss not proved'],
            'pre_action_days' => 14,
        ],
        'Contract Law' => [
            'case_theory' => 'Binding contractual obligations were broken, with direct loss and an available court remedy.',
            'elements' => ['Formation and terms', 'Performance history', 'Breach', 'Loss, mitigation, and remedy'],
            'proof_points' => ['Executed contract and amendments', 'Notice trail', 'Performance chronology', 'Loss calculation'],
            'pressure_points' => ['Condition precedent', 'Notice validity', 'Mitigation', 'Remoteness'],
            'defence_points' => ['No contract/variation', 'No breach', 'Waiver or estoppel', 'Loss too remote'],
            'pre_action_days' => 14,
        ],
        'Property and Landlord Tenant' => [
            'case_theory' => 'Property right, tenancy protection, or possession route has been mishandled or unlawfully resisted.',
            'elements' => ['Tenancy or title status', 'Notice validity', 'Breach or arrears position', 'Possession/repair/remedy entitlement'],
            'proof_points' => ['Lease or tenancy agreement', 'Notice and service evidence', 'Rent ledger or repair record', 'Possession chronology'],
            'pressure_points' => ['Service validity', 'Statutory compliance', 'Arrears accuracy', 'Practical possession timeline'],
            'defence_points' => ['Notice defective', 'Breach not made out', 'Set-off/repair defence', 'Relief from forfeiture or proportionality'],
            'pre_action_days' => 14,
        ],
        'Criminal Law' => [
            'case_theory' => 'The prosecution cannot safely prove each element, or the process is compromised by fairness defects.',
            'elements' => ['Charge particularisation', 'Elements of offence', 'Admissible proof', 'Defence or mitigation position'],
            'proof_points' => ['Charge sheet', 'Witness and interview record', 'Disclosure schedule', 'Custody or bail materials'],
            'pressure_points' => ['Disclosure failure', 'Identification weakness', 'Confession fairness', 'Bail/sentencing exposure'],
            'defence_points' => ['Evidence sufficient', 'Admissions reliable', 'Process regular', 'Public interest requires continuation'],
            'pre_action_days' => 3,
        ],
        'Immigration Law' => [
            'case_theory' => 'The immigration decision is wrong in law, fact, or proportionality and should be reversed, remitted, or restrained.',
            'elements' => ['Route and legal basis', 'Decision error', 'Evidence sufficiency', 'Appeal or review remedy'],
            'proof_points' => ['Decision notice', 'Identity and status record', 'Family/private life evidence', 'Route-specific supporting documents'],
            'pressure_points' => ['Time to appeal', 'Credibility findings', 'Article 8 proportionality', 'Removal risk'],
            'defence_points' => ['Rule not met', 'Credibility issue', 'Decision within lawful margin', 'No disproportionate impact'],
            'pre_action_days' => 7,
        ],
        'Employment Law' => [
            'case_theory' => 'Dismissal, detriment, or discrimination was unlawful and caused compensable workplace loss.',
            'elements' => ['Employment status and continuity', 'Act complained of', 'Fairness/discrimination test', 'Loss and remedy'],
            'proof_points' => ['Contract and policies', 'Disciplinary/grievance trail', 'Comparator or chronology evidence', 'Loss schedule'],
            'pressure_points' => ['Time limit', 'ACAS steps', 'Comparator/protected act proof', 'Polkey/mitigation exposure'],
            'defence_points' => ['Fair reason', 'Reasonable process', 'No discriminatory nexus', 'Loss limited'],
            'pre_action_days' => 14,
        ],
        'Family Law' => [
            'case_theory' => 'The court should make practical welfare-led orders because the current arrangement risks harm, instability, or injustice.',
            'elements' => ['Relevant relationship status', 'Welfare and factual matrix', 'Risk/protective factors', 'Proportionate order sought'],
            'proof_points' => ['Chronology', 'Messages and records', 'Safeguarding material', 'School/medical context'],
            'pressure_points' => ['Interim risk', 'Credibility and consistency', 'Child welfare focus', 'Enforceability of orders'],
            'defence_points' => ['No welfare concern', 'Orders unnecessary', 'Applicant exaggerates', 'Alternative arrangement preferable'],
            'pre_action_days' => 7,
        ],
        'Oil and Gas' => [
            'case_theory' => 'Licence, regulatory, or joint operating rights were breached, exposing the operator or participant to material commercial and compliance loss.',
            'elements' => ['Regulatory or contractual source', 'Default event', 'Operational impact', 'Commercial remedy or protective relief'],
            'proof_points' => ['Licence and permit record', 'JOA or contract text', 'Technical evidence', 'Regulatory notices'],
            'pressure_points' => ['Operator default', 'Environmental exposure', 'Audit/accounting rights', 'Arbitration and notice steps'],
            'defence_points' => ['Compliance maintained', 'Technical causation disputed', 'Risk allocated by JOA', 'No recoverable loss'],
            'pre_action_days' => 14,
        ],
        'Latin Maxims' => [
            'case_theory' => 'A maxim supports the legal proposition but must be tied to the live facts, operative law, and practical relief.',
            'elements' => ['Correct maxim selection', 'Accurate legal proposition', 'Fact-specific application', 'Authority support'],
            'proof_points' => ['Maxim text', 'Meaning and category', 'Contextual legal issue', 'Supporting authority'],
            'pressure_points' => ['Avoid ornament', 'Tie maxim to rule', 'Use for interpretation not substitution', 'Keep application precise'],
            'defence_points' => ['Maxim inapplicable', 'Modern statute controls', 'Principle overstated', 'No factual fit'],
            'pre_action_days' => 14,
        ],
        'International Investment & Arbitration' => [
            'case_theory' => 'State conduct breached treaty protection standards, causing investment loss within arbitral jurisdiction.',
            'elements' => ['Treaty and investor coverage', 'Jurisdiction and admissibility', 'Breach standard', 'Causation, valuation, and relief'],
            'proof_points' => ['Treaty and consent route', 'Investment record', 'Regulatory measures chronology', 'Valuation materials'],
            'pressure_points' => ['Cooling-off and notice', 'Jurisdiction objections', 'Police powers/necessity', 'Quantum methodology'],
            'defence_points' => ['No jurisdiction', 'No protected investment', 'Police powers', 'No compensable causation'],
            'pre_action_days' => 21,
        ],
        default => [
            'case_theory' => 'The pleaded facts must be aligned to the legal wrong, the proof record, and the remedy sought.',
            'elements' => ['Facts', 'Legal source', 'Proof', 'Remedy'],
            'proof_points' => ['Core documents', 'Chronology', 'Witness evidence', 'Relief basis'],
            'pressure_points' => ['Limitation', 'Jurisdiction', 'Proof sufficiency', 'Relief fit'],
            'defence_points' => ['No breach', 'No proof', 'Procedural objection', 'Remedy unavailable'],
            'pre_action_days' => 14,
        ],
    };
}

function aep_counterargument_engine(array $profile, array $case): array
{
    $label = $profile['label'] ?? '';
    return match ($label) {
        'Human Rights' => [
            'The respondent will say the interference was lawful, necessary and proportionate.',
            'The respondent may argue the claimant has not exhausted domestic remedies or proved causation.',
            'The respondent may contest public authority responsibility or the scope of the right engaged.',
        ],
        'Administrative Law' => [
            'The decision-maker will argue the decision was within discretion and supported by reasons.',
            'The public body may say there was no material procedural defect and no better alternative outcome.',
            'Permission, standing, timing and alternative remedy points may be raised.',
        ],
        'Contract Law' => [
            'The other side may deny breach, rely on a contractual exclusion, or say a condition precedent was not met.',
            'They may argue waiver, estoppel, force majeure, or failure to mitigate.',
            'They may say the claimed loss is too remote or not properly proved.',
        ],
        'Commercial Law' => [
            'The counterparty may argue the transaction was allocation-of-risk based and the loss falls within agreed terms.',
            'They may rely on termination rights, limitation clauses, or service-level carve-outs.',
            'They may dispute commercial causation and quantum.',
        ],
        'Property and Landlord Tenant' => [
            'The other side may challenge the validity of the notice, service, or tenancy classification.',
            'They may plead disrepair, set-off, waiver, proportionality, or relief from forfeiture.',
            'They may dispute arrears, breach particulars, or the practical basis for possession.',
        ],
        'Criminal Law' => [
            'The prosecution will say the elements of the offence are proved by direct or circumstantial evidence.',
            'It may resist exclusion arguments and say any interview or identification evidence is fair and reliable.',
            'It will press bail risk, bad character, or sentencing aggravation if the record allows.',
        ],
        'Immigration Law' => [
            'The Secretary of State may say the rule was not met and the evidence was insufficient or late.',
            'Credibility, suitability, deception, or Article 8 proportionality points may be pressed hard.',
            'They may argue any procedural defect was immaterial to the outcome.',
        ],
        'Employment Law' => [
            'The employer will say there was a fair reason and a reasonable process.',
            'It may deny any discriminatory nexus and rely on legitimate management action.',
            'It will likely challenge causation, mitigation, and quantum.',
        ],
        'Family Law' => [
            'The respondent may say the proposed order is unnecessary, disproportionate, or not in the child’s welfare interests.',
            'They may challenge factual allegations, safeguarding concerns, or the applicant’s credibility.',
            'They may offer an alternative arrangement and argue the court should not escalate conflict.',
        ],
        'Tort Law' => [
            'The defendant may deny duty, breach, causation, or foreseeability.',
            'Contributory negligence and failure to mitigate will likely be pleaded.',
            'Quantum and expert evidence may be challenged.',
        ],
        'Oil and Gas' => [
            'The operator or regulator may deny breach and rely on compliance history, technical causation points, or cure rights.',
            'They may argue the contract allocated the risk or that expert evidence does not support liability.',
            'Jurisdiction, arbitration, notice, or audit mechanics may be raised as threshold objections.',
        ],
        'Latin Maxims' => [
            'The other side may say the maxim is decorative only and does not alter the governing statute or contract.',
            'They may argue the maxim has been overstated or detached from the live facts.',
            'They may insist the modern authority route is sufficient without resort to maxim-based framing.',
        ],
        'International Investment & Arbitration' => [
            'The State may challenge jurisdiction, treaty coverage, admissibility, or investor standing.',
            'It may rely on police powers, necessity, public purpose, or proportionate regulation.',
            'Causation and valuation will likely be attacked.',
        ],
        default => [
            'Expect a denial of breach and a challenge to proof, causation, and remedy.',
            'Check for procedural objections, delay, and alternative routes to relief.',
        ],
    };
}

function aep_remedy_engine(array $profile, array $case): array
{
    $label = $profile['label'] ?? '';
    $requested = aep_case_first_non_empty($case, ['remedy', 'relief_sought', 'damages_claimed', 'dispute_amount']);
    $suggested = match ($label) {
        'Human Rights' => ['Declaration', 'Injunction', 'Damages / compensation', 'Release / cessation of interference', 'Policy change'],
        'Administrative Law' => ['Quashing order', 'Mandatory order', 'Prohibiting order', 'Declaration', 'Interim relief'],
        'Contract Law' => ['Damages', 'Specific performance', 'Injunction', 'Declaration', 'Interest and costs'],
        'Commercial Law' => ['Damages', 'Injunction', 'Termination / rescission', 'Declaratory relief', 'Costs and interest'],
        'Property and Landlord Tenant' => ['Possession order', 'Injunction', 'Rent arrears / damages', 'Declaration', 'Costs'],
        'Criminal Law' => ['Bail variation or release', 'Exclusion of evidence', 'Acquittal or dismissal', 'Sentencing mitigation', 'Costs where available'],
        'Immigration Law' => ['Quashing or reconsideration', 'Interim relief against removal', 'Declaration', 'Remittal / remaking', 'Costs where available'],
        'Employment Law' => ['Compensation', 'Reinstatement / re-engagement', 'Declaration', 'Injury to feelings', 'Costs where available'],
        'Family Law' => ['Child arrangements order', 'Prohibited steps order', 'Specific issue order', 'Occupation / non-molestation relief', 'Costs where justified'],
        'Tort Law' => ['Damages', 'Injunction', 'Interest', 'Costs'],
        'Oil and Gas' => ['Damages', 'Injunction', 'Specific performance', 'Declaration', 'Costs and expert relief directions'],
        'Latin Maxims' => ['Declaratory clarification', 'Supportive interpretive submission', 'Costs', 'Ancillary relief matched to the substantive claim'],
        'International Investment & Arbitration' => ['Damages', 'Interest', 'Declaration of breach', 'Costs', 'Post-award enforcement'],
        default => ['Damages', 'Declaration', 'Injunction', 'Costs'],
    };

    return [
        'requested' => $requested,
        'recommended' => $suggested,
        'fit' => $requested !== '' ? 'Requested relief can be benchmarked against the case theory.' : 'Relief should be matched to the proven wrong and the available cause of action.',
    ];
}

function aep_case_date_candidates(array $case): array
{
    $candidates = [
        'decision_date',
        'notice_date',
        'incident_date',
        'violation_date',
        'contract_date',
        'appeal_date',
        'hearing_date',
        'termination_date',
        'arrest_date',
        'dismissal_date',
    ];
    $rows = [];
    foreach ($candidates as $field) {
        if (!empty($case[$field])) {
            $rows[] = [
                'field' => $field,
                'label' => aep_labelize($field),
                'date' => trim((string)$case[$field]),
            ];
        }
    }
    return $rows;
}

function aep_parse_case_date(string $date): ?DateTimeImmutable
{
    $date = trim($date);
    if ($date === '') {
        return null;
    }
    $ts = strtotime($date);
    if ($ts === false) {
        return null;
    }
    return (new DateTimeImmutable())->setTimestamp($ts);
}

function aep_chronology_engine(array $profile, array $case): array
{
    $eventMap = [
        'decision_date' => 'Decision / refusal / determination issued',
        'notice_date' => 'Notice served or received',
        'incident_date' => 'Underlying incident',
        'violation_date' => 'Alleged violation',
        'contract_date' => 'Contract / transaction date',
        'appeal_date' => 'Appeal filed',
        'hearing_date' => 'Hearing date',
        'termination_date' => 'Termination date',
        'arrest_date' => 'Arrest / detention date',
        'dismissal_date' => 'Dismissal date',
    ];
    $rows = [];
    foreach (aep_case_date_candidates($case) as $row) {
        $dt = aep_parse_case_date($row['date']);
        $rows[] = [
            'date' => $row['date'],
            'label' => $row['label'],
            'event' => $eventMap[$row['field']] ?? trim((string)($case['summary'] ?? $case['dispute_summary'] ?? $case['grounds'] ?? '')),
            'sort' => $dt ? $dt->format('Y-m-d') : '9999-12-31',
        ];
    }

    if (empty($rows)) {
        $fallback = trim((string)($case['summary'] ?? $case['dispute_summary'] ?? $case['contract_description'] ?? ''));
        if ($fallback !== '') {
            $rows[] = [
                'date' => '',
                'label' => 'Chronology not fully stated',
                'event' => $fallback,
                'sort' => '9999-12-31',
            ];
        }
    }

    usort($rows, static fn(array $a, array $b) => strcmp($a['sort'], $b['sort']));
    foreach ($rows as &$row) {
        unset($row['sort']);
    }
    unset($row);

    return $rows;
}

function aep_authority_store(): array
{
    return [
        'Human Rights' => [
            ['target' => 'Article 5 liberty and detention', 'principle' => 'Detention must be lawful, prompt, and subject to proper judicial control.', 'source_hint' => 'ECHR / constitutional liberty guarantee', 'patterns' => ['/article\s*5/i', '/detain|arrest|custod/i']],
            ['target' => 'Article 10 expression', 'principle' => 'Speech restrictions require lawful basis, legitimate aim, and proportionality.', 'source_hint' => 'ECHR free expression jurisprudence', 'patterns' => ['/article\s*10/i', '/expression|speech|press|broadcast|censor/i']],
            ['target' => 'Article 14 equality', 'principle' => 'Differential treatment requires objective and reasonable justification.', 'source_hint' => 'Equality / non-discrimination line of authority', 'patterns' => ['/article\s*14/i', '/discrimin/i']],
        ],
        'Administrative Law' => [
            ['target' => 'Illegality / improper purpose', 'principle' => 'Public power must be exercised within statutory limits and for proper purpose.', 'source_hint' => 'Judicial review illegality authorities', 'patterns' => ['/illegal|ultra vires|fetter/i', '/ground of review/i']],
            ['target' => 'Procedural fairness', 'principle' => 'A materially unfair process can vitiate the decision.', 'source_hint' => 'Fair hearing / natural justice authorities', 'patterns' => ['/procedur|bias|fairness/i']],
            ['target' => 'Irrationality / proportionality', 'principle' => 'A decision may fail if irrational or disproportionate on the facts.', 'source_hint' => 'Wednesbury / proportionality authorities', 'patterns' => ['/irrational|proportion/i']],
        ],
        'Tort Law' => [
            ['target' => 'Duty of care', 'principle' => 'Liability starts with a recognized duty relationship and foreseeable harm.', 'source_hint' => 'Negligence duty authorities', 'patterns' => ['/duty/i', '/neglig/i']],
            ['target' => 'Causation and remoteness', 'principle' => 'The claimant must prove causal connection and loss within recoverable scope.', 'source_hint' => 'Causation / remoteness authorities', 'patterns' => ['/caus|remoteness|foresee/i']],
        ],
        'Commercial Law' => [
            ['target' => 'Operative contractual terms', 'principle' => 'Liability turns on what risk the parties actually allocated by the bargain.', 'source_hint' => 'Commercial interpretation authorities', 'patterns' => ['/contract|term|clause|indemn/i']],
            ['target' => 'Termination and default rights', 'principle' => 'Termination depends on breach gravity, notice compliance, and contractual machinery.', 'source_hint' => 'Commercial default / termination authorities', 'patterns' => ['/terminat|default|notice/i']],
        ],
        'Contract Law' => [
            ['target' => 'Formation and certainty', 'principle' => 'Enforcement depends on a concluded bargain with sufficiently certain terms.', 'source_hint' => 'Contract formation authorities', 'patterns' => ['/contract|agreement|consideration/i']],
            ['target' => 'Breach and loss', 'principle' => 'Recoverable relief depends on breach proof, causation, mitigation, and remoteness.', 'source_hint' => 'Contract damages authorities', 'patterns' => ['/breach|loss|mitigat|repudiat/i']],
        ],
        'Property and Landlord Tenant' => [
            ['target' => 'Notice and possession route', 'principle' => 'Possession and enforcement depend on valid notice, service, and statutory compliance.', 'source_hint' => 'Landlord and tenant statutory framework', 'patterns' => ['/notice|service|possession|tenan/i']],
            ['target' => 'Repair and tenancy obligations', 'principle' => 'Liability turns on the lease obligations, condition evidence, and notice of defect.', 'source_hint' => 'Lease covenant / repair authorities', 'patterns' => ['/repair|lease|arrears|deposit/i']],
        ],
        'Criminal Law' => [
            ['target' => 'Burden and standard of proof', 'principle' => 'The prosecution must prove every element beyond reasonable doubt.', 'source_hint' => 'Criminal proof authorities', 'patterns' => ['/charge|offence|proof/i']],
            ['target' => 'Admissibility and fairness', 'principle' => 'Unfairly obtained evidence or flawed identification can undermine the case.', 'source_hint' => 'PACE / fair trial authorities', 'patterns' => ['/confession|interview|identification|search|seizure/i']],
        ],
        'Immigration Law' => [
            ['target' => 'Route requirements and evidential burden', 'principle' => 'The decision must be tested against the rule, the evidence, and any mandatory fairness requirements.', 'source_hint' => 'Immigration rules and appellate principles', 'patterns' => ['/visa|leave|route|refusal/i']],
            ['target' => 'Article 8 proportionality', 'principle' => 'Removal or refusal must be assessed against proportionality and family/private life impact where engaged.', 'source_hint' => 'Article 8 immigration authorities', 'patterns' => ['/article 8/i', '/family|private life|remov|deport/i']],
        ],
        'Employment Law' => [
            ['target' => 'Fair dismissal framework', 'principle' => 'Dismissal requires fair reason and reasonable procedure.', 'source_hint' => 'Unfair dismissal authorities', 'patterns' => ['/dismiss|disciplin|redundan/i']],
            ['target' => 'Discrimination / detriment', 'principle' => 'The tribunal tests treatment, reason why, and causal link to the protected characteristic or act.', 'source_hint' => 'Equality Act authorities', 'patterns' => ['/discrimin|harass|victim|whistle/i']],
        ],
        'Family Law' => [
            ['target' => 'Welfare principle', 'principle' => 'The child’s welfare is the court’s paramount consideration.', 'source_hint' => 'Children Act / welfare authorities', 'patterns' => ['/child|welfare|contact|residence/i']],
            ['target' => 'Protective and interim relief', 'principle' => 'Interim orders depend on risk, urgency, and workable protective arrangements.', 'source_hint' => 'Family protective relief authorities', 'patterns' => ['/domestic abuse|non-molestation|occupation|interim/i']],
        ],
        'Oil and Gas' => [
            ['target' => 'Licence and regulatory compliance', 'principle' => 'Operational rights depend on permit, licence, and environmental compliance.', 'source_hint' => 'Sector regulatory framework', 'patterns' => ['/licen|permit|environment|operator/i']],
            ['target' => 'JOA and risk allocation', 'principle' => 'Joint operating rights and liabilities are controlled by the contractual risk-sharing regime.', 'source_hint' => 'JOA / sector contract authorities', 'patterns' => ['/joint operating|joa|royalt|default/i']],
        ],
        'Latin Maxims' => [
            ['target' => 'Interpretive or equitable maxim', 'principle' => 'The maxim must illuminate the rule, not replace it.', 'source_hint' => 'Interpretive and equitable principle sources', 'patterns' => ['/equity|justice|maxim|interpret/i']],
            ['target' => 'Proof and fairness maxim', 'principle' => 'Use the maxim to sharpen burden, fairness, or procedural framing only where the facts fit.', 'source_hint' => 'Evidence / fairness principle sources', 'patterns' => ['/strict proof|burden|fair/i']],
        ],
        'International Investment & Arbitration' => [
            ['target' => 'Jurisdiction and treaty coverage', 'principle' => 'Arbitral competence depends on consent, protected investment, and treaty scope.', 'source_hint' => 'Treaty arbitration jurisdiction authorities', 'patterns' => ['/treaty|jurisdiction|consent|icsid|uncitral/i']],
            ['target' => 'Substantive treaty protection', 'principle' => 'The measure must be tested against FET, expropriation, FPS, and causation standards.', 'source_hint' => 'Investment treaty merits authorities', 'patterns' => ['/fet|fair and equitable|expropriat|fps|mfn/i']],
        ],
    ];
}

function aep_authority_citation_engine(array $profile, array $case): array
{
    $label = $profile['label'] ?? '';
    $blob = strtolower(aep_case_text_blob($case, ['article_section', 'legal_basis', 'ground_of_review', 'summary', 'grounds', 'dispute_summary', 'contract_description', 'decision_description', 'charges', 'treaty_basis']));
    $results = [];
    $store = aep_authority_store();
    $entries = $store[$label] ?? [];
    $citationHook = aep_case_first_non_empty($case, ['article_section', 'legal_basis', 'ground_of_review', 'charges', 'treaty_basis']);

    foreach ($entries as $entry) {
        foreach ($entry['patterns'] as $pattern) {
            if (preg_match($pattern, $blob) === 1) {
                $results[] = [
                    'bucket' => strtolower($label),
                    'target' => $entry['target'],
                    'principle' => $entry['principle'],
                    'source_hint' => $entry['source_hint'],
                    'citation_hook' => $citationHook,
                    'status' => 'supported',
                ];
                break;
            }
        }
    }

    if (empty($results) && !empty($entries)) {
        foreach (array_slice($entries, 0, 2) as $entry) {
            $results[] = [
                'bucket' => strtolower($label),
                'target' => $entry['target'],
                'principle' => $entry['principle'],
                'source_hint' => $entry['source_hint'],
                'citation_hook' => $citationHook,
                'status' => 'check',
            ];
        }
    }

    if (empty($results)) {
        $results[] = [
            'bucket' => 'general',
            'target' => 'Authorities need to be checked against the pleaded cause of action.',
            'principle' => 'Pin the legal source to the pleaded wrong before citing anything.',
            'source_hint' => 'Use the operative statute, contract, rule, or jurisdictional instrument.',
            'citation_hook' => $citationHook,
            'status' => 'needs review',
        ];
    }

    return $results;
}

function aep_fact_to_issues_engine(array $profile, array $case): array
{
    $rows = [];
    $pairs = [
        'title' => 'Matter identification',
        'claimant' => 'Who brings the claim',
        'respondent' => 'Who must answer it',
        'article_section' => 'Legal source',
        'ground_of_review' => 'Ground of challenge',
        'transaction_type' => 'Transaction type',
        'tort_type' => 'Cause in tort',
        'relief_sought' => 'Relief sought',
        'remedy' => 'Remedy sought',
        'summary' => 'Core facts',
        'dispute_summary' => 'Core dispute',
        'grounds' => 'Grounds pleaded',
    ];

    foreach ($pairs as $field => $issue) {
        if (aep_case_field_present($case, $field)) {
            $rows[] = [
                'fact' => aep_labelize($field) . ': ' . trim((string)$case[$field]),
                'issue' => $issue,
                'use' => 'Fits the live case theory.',
            ];
        }
    }

    foreach ($profile['issue_fields'] ?? [] as $field) {
        if (!aep_case_field_present($case, $field)) {
            $rows[] = [
                'fact' => aep_labelize($field),
                'issue' => 'Missing fact',
                'use' => 'This needs evidence or pleading support before final advice.',
            ];
        }
    }

    return $rows;
}

function aep_procedure_limitation_engine(array $profile, array $case): array
{
    $label = $profile['label'] ?? '';
    $dateFields = aep_case_date_candidates($case);
    $primaryDate = $dateFields[0]['date'] ?? '';
    $parsed = aep_parse_case_date($primaryDate);
    $today = new DateTimeImmutable('today');
    $ageDays = $parsed ? (int)$today->diff($parsed)->format('%r%a') : null;

    $procedure = match ($label) {
        'Human Rights' => ['Track' => 'rights claim / constitutional or civil rights track', 'Limitation' => 'Check promptness, exhaustion, and any statutory time limit'],
        'Administrative Law' => ['Track' => 'judicial review / public law track', 'Limitation' => 'Check prompt filing and permission requirements'],
        'Contract Law', 'Commercial Law' => ['Track' => 'civil claim / commercial track', 'Limitation' => 'Check contractual limitation, statutory limitation, and pre-action steps'],
        'Property and Landlord Tenant' => ['Track' => 'civil property / possession track', 'Limitation' => 'Check notice validity, possession route, arrears period, and statutory preconditions'],
        'Criminal Law' => ['Track' => 'criminal procedure track', 'Limitation' => 'Check custody time limits, charge stage, disclosure timetable, and hearing listing'],
        'Immigration Law' => ['Track' => 'tribunal / immigration review track', 'Limitation' => 'Check appeal deadline, administrative review timing, and removal urgency'],
        'Employment Law' => ['Track' => 'employment tribunal track', 'Limitation' => 'Check tribunal limitation, ACAS timing, and continuing act arguments'],
        'Family Law' => ['Track' => 'family court track', 'Limitation' => 'Check urgency, safeguarding listing, and whether interim relief should be sought now'],
        'Tort Law' => ['Track' => 'civil claim / tort track', 'Limitation' => 'Check the primary limitation period and any extension arguments'],
        'Oil and Gas' => ['Track' => 'commercial / regulatory / arbitration track', 'Limitation' => 'Check notice periods, contractual time bars, regulator deadlines, and arbitration prerequisites'],
        'Latin Maxims' => ['Track' => 'supporting legal research track', 'Limitation' => 'Check the substantive case deadline; the maxim itself does not alter time limits'],
        'International Investment & Arbitration' => ['Track' => 'arbitration / treaty track', 'Limitation' => 'Check treaty time bars, jurisdiction, cooling-off, and notice requirements'],
        default => ['Track' => 'civil litigation track', 'Limitation' => 'Check the applicable time bar and filing route'],
    };

    $risk = 'Low';
    if ($ageDays !== null && $ageDays > 365) {
        $risk = 'High';
    } elseif ($ageDays !== null && $ageDays > 180) {
        $risk = 'Medium';
    }

    return [
        'track' => $procedure['Track'],
        'limitation' => $procedure['Limitation'],
        'primary_date' => $primaryDate,
        'age_days' => $ageDays,
        'risk' => $risk,
        'next_step' => $ageDays !== null ? 'Compare the primary date against the filing deadline and any pre-action timetable.' : 'Identify the decisive date first. Limitation cannot be checked until the trigger date is fixed.',
    ];
}

function aep_pleading_generator_engine(array $profile, array $case): array
{
    $label = $profile['label'] ?? 'Matter';
    $title = aep_case_first_non_empty($case, ['title', 'case_reference', 'maxim']);
    $facts = aep_case_text_blob($case, ['summary', 'grounds', 'dispute_summary', 'dispute_description', 'contract_description', 'breach_description']);
    $issueLines = [];
    foreach (array_slice(aep_fact_to_issues_engine($profile, $case), 0, 6) as $row) {
        $issueLines[] = ($row['issue'] ?? 'Issue') . ': ' . ($row['fact'] ?? ''); 
    }

    $intro = match ($label) {
        'Human Rights' => 'The Claimant alleges breach of protected rights and seeks declarations, relief, and compensation.',
        'Administrative Law' => 'The Claimant alleges public law unlawfulness and seeks supervisory relief.',
        'Contract Law' => 'The Claimant alleges contractual breach and seeks damages and consequential relief.',
        'Commercial Law' => 'The Claimant alleges commercial breach and seeks damages and protective relief.',
        'Property and Landlord Tenant' => 'The Claimant alleges breach of property or tenancy rights and seeks possession, compliance, or compensatory relief.',
        'Criminal Law' => 'The Defendant disputes the prosecution case and seeks bail, exclusion, dismissal, acquittal, or proportionate sentence outcome as the record permits.',
        'Immigration Law' => 'The claimant/appellant challenges the immigration decision and seeks reversal, remittal, or urgent protective relief.',
        'Employment Law' => 'The Claimant alleges unfair dismissal, detriment, or discrimination and seeks compensatory and declaratory relief.',
        'Family Law' => 'The Applicant seeks practical family court orders grounded in welfare, safety, and proportionality.',
        'Tort Law' => 'The Claimant alleges tortious wrong and seeks damages and any protective orders.',
        'Oil and Gas' => 'The Claimant alleges regulatory or contractual breach in the energy project and seeks commercial and protective relief.',
        'Latin Maxims' => 'The party relies on the maxim as a supporting legal principle tied to the pleaded issue and remedy.',
        'International Investment & Arbitration' => 'The Claimant alleges treaty breach and seeks damages, interest, and declaratory relief.',
        default => 'The Claimant alleges legal wrong and seeks appropriate relief.',
    };

    return [
        'heading' => $title !== '' ? $title : ($label . ' pleading'),
        'title' => $title !== '' ? $title : $label,
        'opening' => $intro,
        'facts' => $facts !== '' ? $facts : 'Facts to be completed from the file.',
        'issues' => $issueLines,
        'cause' => 'The pleaded cause of action should track the facts, the legal source, and the remedy.',
        'conclusion' => 'Plead breach, proof, and relief with precision. Do not overstate the case.',
    ];
}

function aep_relief_drafting_engine(array $profile, array $case): array
{
    $label = $profile['label'] ?? '';
    $remedy = aep_remedy_engine($profile, $case);
    $drafts = match ($label) {
        'Human Rights' => [
            'A declaration that the claimant’s rights were breached.',
            'An order restraining continuation of the unlawful interference.',
            'Damages and costs.',
        ],
        'Administrative Law' => [
            'A quashing order.',
            'A mandatory or prohibiting order as appropriate.',
            'A declaration and costs.',
        ],
        'Contract Law', 'Commercial Law' => [
            'Damages for breach.',
            'Interest and costs.',
            'Any injunction or declaratory relief required to protect performance or enforcement.',
        ],
        'Property and Landlord Tenant' => [
            'A possession, compliance, or declaratory order as the tenancy position requires.',
            'Arrears, damages, interest, and costs.',
            'Any injunction needed to preserve the property position pending final determination.',
        ],
        'Criminal Law' => [
            'Bail, exclusion, dismissal, or acquittal relief as supported by the record.',
            'In the alternative, the narrowest lawful finding with full credit for mitigation.',
            'Any ancillary direction required for fairness, disclosure, or case management.',
        ],
        'Immigration Law' => [
            'An order setting aside or remitting the immigration decision.',
            'Interim relief restraining removal or enforcement where urgency is proved.',
            'Such further declaratory or consequential relief as the tribunal or court permits.',
        ],
        'Employment Law' => [
            'Compensation for financial loss and injury to feelings where available.',
            'Reinstatement or re-engagement if legally viable and strategically sound.',
            'A declaration, interest, and costs where the forum permits.',
        ],
        'Family Law' => [
            'A welfare-led order defining arrangements, restrictions, or protective measures.',
            'Any interim safeguarding relief required immediately.',
            'Costs only if justified by the conduct and forum rules.',
        ],
        'Tort Law' => [
            'Damages for loss and injury.',
            'Interest and costs.',
            'Any interim or protective injunction supported by the facts.',
        ],
        'Oil and Gas' => [
            'Damages, debt, or accounting relief for the pleaded commercial default.',
            'Protective injunctive or declaratory relief preserving licence, asset, or project position.',
            'Interest, costs, and any expert or document production directions needed.',
        ],
        'Latin Maxims' => [
            'Use the maxim to support the substantive relief, not as standalone relief.',
            'Frame the prayer by the underlying cause of action and the live legal wrong.',
            'Seek costs and declaratory support only where the substantive claim allows it.',
        ],
        'International Investment & Arbitration' => [
            'Damages for treaty breach.',
            'Interest to the award date and post-award interest.',
            'Declaration of breach and costs.',
        ],
        default => [
            'Appropriate declaratory relief.',
            'Damages where available.',
            'Costs and any protective order supported by the record.',
        ],
    };

    return [
        'requested' => $remedy['requested'] ?? '',
        'draft_prayers' => $drafts,
        'fit' => $remedy['fit'] ?? '',
        'short_form' => 'Seek the narrowest order that fully answers the wrong and matches the evidence.',
    ];
}

function aep_domain_strategy_engine(array $profile, array $case): array
{
    $label = $profile['label'] ?? 'General';
    $blueprint = aep_domain_engine_blueprint($label);
    $missing = [];
    foreach (array_slice($profile['issue_fields'] ?? [], 0, 3) as $field) {
        if (!aep_case_field_present($case, $field)) {
            $missing[] = aep_labelize($field);
        }
    }

    return [
        'case_theory' => $blueprint['case_theory'],
        'case_anchor' => aep_case_first_non_empty($case, ['summary', 'dispute_summary', 'grounds', 'contract_description', 'decision_description', 'investment_description', 'meaning']),
        'elements' => $blueprint['elements'],
        'proof_points' => $blueprint['proof_points'],
        'pressure_points' => $blueprint['pressure_points'],
        'defence_points' => $blueprint['defence_points'],
        'file_focus' => empty($missing)
            ? 'Core pleaded issue fields are present. Tighten proof, sequence, and remedy fit.'
            : 'Fix these file gaps first: ' . implode(', ', $missing) . '.',
    ];
}

function aep_skeleton_argument_engine(array $profile, array $case): array
{
    $strategy = aep_domain_strategy_engine($profile, $case);
    $authorities = aep_authority_citation_engine($profile, $case);
    $factMap = aep_fact_to_issues_engine($profile, $case);
    $remedy = aep_remedy_engine($profile, $case);
    $title = aep_case_first_non_empty($case, ['title', 'case_reference', 'maxim']);

    $issues = [];
    foreach (array_slice($factMap, 0, 4) as $row) {
        $issues[] = ($row['issue'] ?? 'Issue') . ': ' . ($row['fact'] ?? '');
    }

    $authorityLines = [];
    foreach (array_slice($authorities, 0, 3) as $row) {
        $authorityLines[] = ($row['target'] ?? 'Authority') . ' - ' . ($row['principle'] ?? '');
    }

    return [
        'heading' => ($title !== '' ? $title : ($profile['label'] ?? 'Matter')) . ' skeleton argument',
        'standard' => 'Lead with the legal test. Tie each proposition to a fact, a document, and the relief sought.',
        'issues' => $issues,
        'propositions' => [
            'The pleaded facts disclose an arguable ' . strtolower((string) ($profile['label'] ?? 'legal')) . ' claim or answer.',
            'The core legal elements can be proved if the identified documents and dates are pinned down.',
            'The opponent case is vulnerable on the main pressure points identified below.',
            'The relief sought is available if the court or tribunal accepts the primary factual theory.',
        ],
        'authorities' => $authorityLines,
        'relief_line' => 'Orders sought: ' . implode('; ', array_slice($remedy['recommended'] ?? [], 0, 3)),
    ];
}

function aep_letter_before_action_engine(array $profile, array $case): array
{
    $label = $profile['label'] ?? 'Matter';
    $blueprint = aep_domain_engine_blueprint($label);
    $claimant = aep_case_first_non_empty($case, ['claimant', 'claimant_name', 'client_name', 'petitioner', 'investor_name', 'landlord_name']);
    $respondent = aep_case_first_non_empty($case, ['respondent', 'respondent_name', 'defendant_name', 'opponent_name', 'counterparty_name', 'tenant_name', 'regulatory_body', 'respondent_state']);
    $title = aep_case_first_non_empty($case, ['title', 'case_reference', 'maxim']);
    $violations = aep_legal_violation_engine($profile, $case);
    $remedy = aep_remedy_engine($profile, $case);

    return [
        'subject' => ($title !== '' ? $title : $label) . ' - pre-action demand',
        'opening' => 'We act for ' . ($claimant !== '' ? $claimant : 'our client') . '. This is formal notice of the claim arising from the matters set out in this file.',
        'recipient' => $respondent !== '' ? $respondent : 'the opposing party',
        'breaches' => array_map(static fn(array $row) => $row['label'] ?? 'Legal wrong', array_slice($violations, 0, 3)),
        'demands' => array_slice($remedy['recommended'] ?? [], 0, 3),
        'deadline' => 'Provide a full response within ' . (int) ($blueprint['pre_action_days'] ?? 14) . ' days.',
        'escalation' => 'Failing compliance, proceedings, urgent relief, costs, and disclosure applications will be considered without further notice.',
    ];
}

function aep_bundle_disclosure_engine(array $profile, array $case): array
{
    $label = $profile['label'] ?? '';
    $chronology = aep_chronology_engine($profile, $case);
    $evidence = aep_evidence_matrix_engine($profile, $case);
    $authorities = aep_authority_citation_engine($profile, $case);
    $priorityDocuments = [];
    $missingDocuments = [];

    foreach ($evidence as $row) {
        if (($row['status'] ?? '') === 'present') {
            $priorityDocuments[] = $row['source'] ?? 'Document set';
        } else {
            $missingDocuments[] = $row['source'] ?? 'Missing document set';
        }
    }

    $disclosureRequests = match ($label) {
        'Human Rights' => ['Detention / decision records', 'Custody or policy records', 'Internal review and complaint materials'],
        'Administrative Law' => ['Decision record and reasons', 'Policy or guidance relied on', 'Consultation and fairness record'],
        'Tort Law' => ['Incident report', 'Medical or technical evidence', 'Loss and mitigation documents'],
        'Commercial Law', 'Contract Law' => ['Executed contract and amendments', 'Notice / email chain', 'Payment and performance records'],
        'Property and Landlord Tenant' => ['Lease or tenancy agreement', 'Rent ledger and notice service proof', 'Inspection / repair records'],
        'Criminal Law' => ['Full prosecution disclosure', 'Interview/custody materials', 'CCTV / forensic / identification record'],
        'Immigration Law' => ['Decision notice and casework record', 'Application bundle', 'Country / policy materials if relied on'],
        'Employment Law' => ['Contract and policy documents', 'Disciplinary / grievance papers', 'Comparator or payroll materials'],
        'Family Law' => ['Safeguarding records', 'Messages / care chronology', 'School / medical materials'],
        'Oil and Gas' => ['Licence and permit record', 'JOA / project documents', 'Technical and environmental reports'],
        'Latin Maxims' => ['Underlying substantive pleadings', 'Authority note supporting the maxim', 'Context documents showing the maxim fits'],
        'International Investment & Arbitration' => ['Treaty and consent materials', 'Regulatory measure record', 'Valuation and quantum documents'],
        default => ['Core documents', 'Correspondence', 'Chronology support'],
    };

    return [
        'sections' => [
            'Core pleadings / charge / decision documents',
            'Chronology and issue map',
            'Primary documents',
            'Witness and expert materials',
            'Authorities and research',
            'Relief / quantum materials',
        ],
        'priority_documents' => array_values(array_unique(array_merge(array_slice($priorityDocuments, 0, 5), !empty($chronology) ? ['Chronology documents'] : [], !empty($authorities) ? ['Authorities note'] : []))),
        'missing_documents' => array_values(array_unique(array_slice($missingDocuments, 0, 5))),
        'disclosure_requests' => $disclosureRequests,
    ];
}

function aep_counsel_package(string $domain, array $case, string $docType, string $instructions = '', string $reasoningDepth = 'standard', string $focus = ''): array
{
    $reasoningOptions = aep_reasoning_depth_options();
    if (!isset($reasoningOptions[$reasoningDepth])) {
        $reasoningDepth = 'standard';
    }
    $focus = trim($focus);
    $profile = aep_supported_domain($domain);
    if (!$profile) {
        return [
            'label' => 'Unknown domain',
            'summary' => '',
            'score' => 0,
            'total' => 0,
            'pct' => 0,
            'rating' => 'Unknown',
            'gaps' => [],
            'essentials' => [],
            'engine_status' => [],
            'issues' => [],
            'arguments' => [],
            'draft_outline' => [],
            'next_steps' => [],
            'instructions' => $instructions,
            'reasoning_depth' => $reasoningDepth,
            'focus' => $focus,
            'outcome_v2' => [],
            'domain_strategy' => [],
            'skeleton_argument' => [],
            'letter_before_action' => [],
            'bundle_disclosure' => [],
        ];
    }

    [$found, $total, $pct, $rating, $gaps] = aep_case_score($profile, $case);

    $issues = [];
    foreach ($profile['issue_fields'] as $field) {
        if (!empty($case[$field])) {
            $issues[] = aep_labelize($field) . ': ' . $case[$field];
        }
    }

    $arguments = $profile['arguments'];
    if ($docType === 'analysis_brief') {
        array_unshift($arguments, 'Analysis brief requested. Focus on legal merits, evidential gaps and practical next steps.');
    } elseif ($docType === 'draft_outline') {
        array_unshift($arguments, 'Draft outline requested. Use the case facts to build a structured pleading or note.');
    } elseif ($docType === 'instructions_note') {
        array_unshift($arguments, 'Instructions note requested. Capture the client objective, timeline and action list.');
    }
    if ($reasoningDepth === 'advanced') {
        $arguments[] = 'Advanced depth selected. Add counter-position testing, procedural route choice, and fallback strategy.';
    } elseif ($reasoningDepth === 'aggressive') {
        $arguments[] = 'Aggressive depth selected. State decisive conclusions, press pressure points, and front-load weaknesses in the opponent case.';
    }
    if ($focus !== '') {
        $arguments[] = 'Subdomain focus selected: ' . aep_labelize(str_replace('-', ' ', $focus)) . '.';
    }

    $draftOutline = [
        'Client and matter overview',
        'Facts and chronology',
        'Key legal issues',
        'Merits assessment',
        'Risk points and weaknesses',
        'Recommended strategy',
        'Draft relief or next-step request',
    ];

    $summary = aep_case_summary($profile, $case);
    if ($instructions !== '') {
        $summary .= ($summary !== '' ? ' | ' : '') . 'Client instructions: ' . $instructions;
    }
    if ($focus !== '') {
        $summary .= ($summary !== '' ? ' | ' : '') . 'Subdomain focus: ' . aep_labelize(str_replace('-', ' ', $focus));
    }

    $outcomeV2 = aep_outcome_engine_v2($profile, $case, $found, $total, $pct);
    $violationEngine = aep_legal_violation_engine($profile, $case);
    $clauseEngine = aep_clause_obligation_engine($profile, $case);
    $issueEngine = aep_issue_spotting_engine($profile, $case);
    $evidenceMatrix = aep_evidence_matrix_engine($profile, $case);
    $counterarguments = aep_counterargument_engine($profile, $case);
    $remedyEngine = aep_remedy_engine($profile, $case);
    $chronologyEngine = aep_chronology_engine($profile, $case);
    $authorityEngine = aep_authority_citation_engine($profile, $case);
    $factToIssuesEngine = aep_fact_to_issues_engine($profile, $case);
    $procedureEngine = aep_procedure_limitation_engine($profile, $case);
    $pleadingEngine = aep_pleading_generator_engine($profile, $case);
    $reliefDraftEngine = aep_relief_drafting_engine($profile, $case);
    $domainStrategy = aep_domain_strategy_engine($profile, $case);
    if ($focus !== '') {
        $domainStrategy['focus'] = aep_labelize(str_replace('-', ' ', $focus));
        if (!empty($domainStrategy['file_focus'])) {
            $domainStrategy['file_focus'] .= ' | Focus: ' . $domainStrategy['focus'];
        } else {
            $domainStrategy['file_focus'] = 'Focus: ' . $domainStrategy['focus'];
        }
    }
    $skeletonArgument = aep_skeleton_argument_engine($profile, $case);
    $letterBeforeAction = aep_letter_before_action_engine($profile, $case);
    $bundleDisclosure = aep_bundle_disclosure_engine($profile, $case);

    $engineReady = [
        'outcome_v2' => aep_has_material_content($outcomeV2),
        'legal_violation' => aep_has_material_content($violationEngine),
        'clause_obligation' => aep_has_material_content($clauseEngine),
        'issue_spotting' => aep_has_material_content($issueEngine),
        'evidence_matrix' => aep_has_material_content($evidenceMatrix),
        'counterargument' => aep_has_material_content($counterarguments),
        'remedy' => aep_has_material_content($remedyEngine),
        'chronology' => aep_has_material_content($chronologyEngine),
        'authority_citation' => aep_has_material_content($authorityEngine),
        'fact_to_issues' => aep_has_material_content($factToIssuesEngine),
        'procedure_limitation' => aep_has_material_content($procedureEngine),
        'pleading_generator' => aep_has_material_content($pleadingEngine),
        'relief_drafting' => aep_has_material_content($reliefDraftEngine),
        'domain_strategy' => aep_has_material_content($domainStrategy),
        'skeleton_argument' => aep_has_material_content($skeletonArgument),
        'letter_before_action' => aep_has_material_content($letterBeforeAction),
        'bundle_disclosure' => aep_has_material_content($bundleDisclosure),
    ];

    if (!$engineReady['legal_violation']) {
        $violationEngine = [[
            'label' => 'No mapped violation yet',
            'rule' => 'Populate legal basis, breach, and core facts',
            'conduct' => 'Current record is too thin for violation mapping',
            'proof_needed' => 'Decision/contract/incident records and chronology',
            'remedy_hint' => 'Build facts first, then refresh analysis',
        ]];
    }
    if (!$engineReady['clause_obligation']) {
        $clauseEngine = [[
            'clause' => 'No clause mapped yet',
            'text' => 'Add contract, policy, decision, or statutory wording to map obligations.',
            'risk' => 'Obligation analysis is currently unsupported by the record.',
            'action' => 'Upload or enter the controlling document text.',
        ]];
    }
    if (!$engineReady['issue_spotting']) {
        $issueEngine = [[
            'issue' => 'Issue map incomplete',
            'why_it_matters' => 'Key facts or legal basis are missing.',
            'priority' => 'High',
            'next_move' => 'Complete summary, grounds, and relief fields before final drafting.',
        ]];
    }
    if (!$engineReady['evidence_matrix']) {
        $evidenceMatrix = [[
            'item' => 'Evidence schedule not generated',
            'status' => 'missing',
            'importance' => 'Add documentary and chronology material to activate matrix output.',
        ]];
    }
    if (!$engineReady['counterargument']) {
        $counterarguments = ['No counterarguments generated yet. Add opponent case points or defence theories.'];
    }
    if (!$engineReady['chronology']) {
        $chronologyEngine = [[
            'date' => '',
            'label' => 'Chronology not fixed',
            'event' => 'Enter key dates to unlock procedural and merits sequencing.',
        ]];
    }
    if (!$engineReady['authority_citation']) {
        $authorityEngine = [[
            'target' => 'Authority mapping pending',
            'status' => 'fallback',
            'principle' => 'Add legal basis and issue text for route-specific citation hooks.',
            'source_hint' => 'Use leading statute/rule/case references for the selected domain.',
            'citation_hook' => 'Re-run after adding legal grounds.',
        ]];
    }
    if (!$engineReady['fact_to_issues']) {
        $factToIssuesEngine = [[
            'fact' => 'No mapped facts',
            'issue' => 'Issue linkage unavailable',
            'use' => 'Add summary, grounds, and legal basis to build fact-to-issue mapping.',
        ]];
    }
    if (!$engineReady['procedure_limitation']) {
        $procedureEngine = [
            'track' => 'Undetermined',
            'limitation' => 'Primary date missing',
            'primary_date' => '',
            'age_days' => null,
            'risk' => 'Medium',
            'next_step' => 'Enter at least one core date to run limitation risk properly.',
        ];
    }
    if (!$engineReady['pleading_generator']) {
        $pleadingEngine = [
            'heading' => ($profile['label'] ?? 'Matter') . ' pleading',
            'title' => ($profile['label'] ?? 'Matter'),
            'opening' => 'Pleading draft requires fuller facts and legal basis.',
            'facts' => 'No coherent fact narrative found.',
            'issues' => ['Issue schedule cannot be generated from current record.'],
            'cause' => 'Insert legal basis and route.',
            'conclusion' => 'Complete case essentials and regenerate.',
        ];
    }
    if (!$engineReady['relief_drafting']) {
        $reliefDraftEngine = [
            'draft_prayers' => ['Relief draft pending completion of legal basis and evidence context.'],
            'rationale' => 'Relief must map to proven facts and legal source.',
            'risk_warning' => 'Do not plead broad remedies without evidential support.',
        ];
    }
    if (!$engineReady['domain_strategy']) {
        $domainStrategy = aep_domain_engine_blueprint($profile['label'] ?? '');
    }
    if (!$engineReady['skeleton_argument']) {
        $skeletonArgument = [
            'propositions' => [
                'The case theory is not fully populated from current record.',
                'Core facts and legal basis must be clarified before final argument.',
            ],
            'relief_line' => 'Relief depends on proving the pleaded case with documents.',
        ];
    }
    if (!$engineReady['letter_before_action']) {
        $letterBeforeAction = [
            'opening' => 'Pre-action draft pending complete matter facts and legal basis.',
            'demands' => ['State breach/decision error precisely', 'Provide supporting documents', 'Confirm remedial proposal'],
            'deadline' => 'Set deadline after procedural track is confirmed.',
        ];
    }
    if (!$engineReady['bundle_disclosure']) {
        $bundleDisclosure = [
            'sections' => ['Core pleadings', 'Chronology', 'Primary documents', 'Authorities'],
            'priority_documents' => ['Case summary', 'Legal basis material', 'Key chronology records'],
            'missing_documents' => ['Controlling documents not yet identified'],
            'disclosure_requests' => ['Request missing primary records from the opposing side or decision-maker'],
        ];
    }

    $engineStatus = [];
    foreach (aep_engine_stack_labels() as $key => $labelName) {
        $engineStatus[] = [
            'key' => $key,
            'label' => $labelName,
            'status' => !empty($engineReady[$key]) ? 'ready' : 'fallback',
        ];
    }

    $readyCount = count(array_filter($engineReady, static fn($ready) => (bool)$ready));
    $totalEngines = count($engineReady);
    $analysis = [];
    $analysis[] = 'Posture: ' . strtolower($rating) . ' (' . $pct . '% complete).';
    $analysis[] = 'Evidence: ' . $outcomeV2['evidence_coverage'] . '%. Procedure: ' . $outcomeV2['procedural_readiness'] . '%.';
    $analysis[] = 'Domain theory: ' . ($domainStrategy['case_theory'] ?? 'Match facts, proof, and relief.');
    $analysis[] = 'Engine health: ' . $readyCount . '/' . $totalEngines . ' ready (' . ($totalEngines > 0 ? (int)round(($readyCount / $totalEngines) * 100) : 0) . '%).';
    $analysis[] = 'Reasoning depth: ' . ($reasoningOptions[$reasoningDepth] ?? 'Standard') . '.';
    if (!empty($issues)) {
        $analysis[] = 'Key recorded facts: ' . implode('; ', array_slice($issues, 0, 3)) . '.';
    }
    $analysis[] = 'Trajectory: ' . $outcomeV2['scenario_likely'];
    $analysis[] = 'Risk: ' . (is_array($outcomeV2['recommendations']) && !empty($outcomeV2['recommendations']) ? $outcomeV2['recommendations'][0] : 'Refine the record.');
    if ($domain === 'human_rights') {
        $analysis[] = 'Human rights frame: right, interference, justification, remedy.';
    }
    if ($reasoningDepth !== 'standard') {
        $analysis[] = 'Counter-position: ' . implode('; ', array_slice($counterarguments, 0, 2)) . '.';
        $analysis[] = 'Procedure choice: ' . ($procedureEngine['track'] ?? 'Track to be confirmed') . '.';
    }
    if ($focus !== '') {
        $analysis[] = 'Subdomain focus: ' . aep_labelize(str_replace('-', ' ', $focus)) . '.';
    }
    if ($reasoningDepth === 'aggressive') {
        $analysis[] = 'Decisive line: press admissibility, proof gaps, and remedy leverage now.';
    }

    $reasonedResponse = [];
    $reasonedResponse[] = 'Treat this as a ' . strtolower($rating) . ' case.';
    $reasonedResponse[] = 'The test, proof, and remedy control the outcome.';
    if ($domain === 'human_rights') {
        $reasonedResponse[] = 'For Human Rights, state the right, the interference, the justification test, and the remedy.';
    }
    if (!empty($issues)) {
        $reasonedResponse[] = 'Best facts: ' . implode('; ', array_slice($issues, 0, 2)) . '.';
    }
    if (!empty($gaps)) {
        $reasonedResponse[] = 'Missing: ' . implode(', ', array_map('aep_labelize', array_slice($gaps, 0, 4))) . '.';
    }
    if (!empty($domainStrategy['pressure_points'])) {
        $reasonedResponse[] = 'Pressure points: ' . implode('; ', array_slice($domainStrategy['pressure_points'], 0, 3)) . '.';
    }
    if ($reasoningDepth !== 'standard') {
        $reasonedResponse[] = 'Apply law to fact: tie each element to one document and one contradiction in the opponent account.';
        $reasonedResponse[] = 'Litigation path: commit to the strongest route and hold fallback relief in reserve.';
    }
    if ($focus !== '') {
        $reasonedResponse[] = 'Subdomain focus: ' . aep_labelize(str_replace('-', ' ', $focus)) . '.';
    }
    if ($reasoningDepth === 'aggressive') {
        $reasonedResponse[] = 'Bottom line: attack admissibility and credibility early; force the opponent to prove every element strictly.';
    }
    $reasonedResponse[] = 'Press the strongest claim. Fix the gaps. Match relief to proof.';

    return [
        'label' => $profile['label'],
        'summary' => $summary,
        'analysis' => $analysis,
        'reasoned_response' => implode("\n\n", $reasonedResponse),
        'violations' => $violationEngine,
        'clauses' => $clauseEngine,
        'issue_engine' => $issueEngine,
        'evidence_matrix' => $evidenceMatrix,
        'counterarguments' => $counterarguments,
        'remedies' => $remedyEngine,
        'chronology' => $chronologyEngine,
        'authority_citation' => $authorityEngine,
        'fact_to_issues' => $factToIssuesEngine,
        'procedure_limitation' => $procedureEngine,
        'pleading_generator' => $pleadingEngine,
        'relief_drafting' => $reliefDraftEngine,
        'domain_strategy' => $domainStrategy,
        'skeleton_argument' => $skeletonArgument,
        'letter_before_action' => $letterBeforeAction,
        'bundle_disclosure' => $bundleDisclosure,
        'score' => $found,
        'total' => $total,
        'pct' => $pct,
        'rating' => $rating,
        'gaps' => $gaps,
        'essentials' => aep_domain_essentials($profile),
        'engine_status' => $engineStatus,
        'issues' => $issues,
        'arguments' => $arguments,
        'draft_outline' => $draftOutline,
        'next_steps' => $profile['next_steps'],
        'instructions' => $instructions,
        'reasoning_depth' => $reasoningDepth,
        'focus' => $focus,
        'outcome_v2' => $outcomeV2,
    ];
}
