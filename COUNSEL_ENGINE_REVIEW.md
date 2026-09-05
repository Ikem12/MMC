# Counsel Engine review

The Counsel Engine records a question and factual instruction against a matter.
It assesses whether the instruction includes a chronology, dates, and evidence,
then generates a transparent readiness score and low/medium/high risk triage.

Every completed review is persisted in `p2_counsel_reviews` and logged in the
matter activity trail. The review screen supplies a professional checklist for
jurisdiction, limitation, evidence, costs, and settlement considerations.

It deliberately does not present automated output as final legal advice.
