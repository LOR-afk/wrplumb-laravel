@php
    $requestData = $contract->quotation->request;
    $serviceCategory = $requestData->service_category ?? 'Plumbing';
    $serviceType = $requestData->service_type ?? 'Installation Services';
    $projectDescription = trim($serviceCategory . ' / ' . $serviceType);

    $contractDate = $contract->contract_date ?? $contract->created_at;
    $contractYear = optional($contractDate)->format('Y') ?? now()->format('Y');

    $amount = (float) $contract->total_contract_price;

    if (class_exists(\NumberFormatter::class)) {
        $formatter = new \NumberFormatter('en', \NumberFormatter::SPELLOUT);
        $amountInWords = strtoupper($formatter->format((int) round($amount)));
    } else {
        $amountInWords = strtoupper(number_format($amount, 2));
    }

    $paymentPhases = data_get($contract->payment_terms, 'phases', []);
@endphp

<article class="legal-contract-document">
    <section class="legal-contract-page legal-page-one">
        <header class="legal-document-header">
            <h1>CONTRACT AGREEMENT</h1>
            <h2>{{ strtoupper($serviceType) }}</h2>
        </header>

        <div class="legal-document-body">
            <p class="legal-opening-title">KNOW ALL MEN BY THESE PRESENTS:</p>

            <p>
                This Contract Agreement, hereinafter referred to as the
                <strong>“Agreement”</strong>, is entered into and executed this
                <strong>{{ optional($contractDate)->format('jS') ?? '___' }}</strong>
                day of
                <strong>{{ optional($contractDate)->format('F, Y') ?? '__________' }}</strong>,
                in Cagayan de Oro City, Philippines, by and between:
            </p>

            <p class="legal-party-paragraph">
                <strong>WR PLUMBING AND CONSTRUCTION SERVICES</strong>, a sole
                proprietorship business entity duly organized and existing under the
                laws of the Republic of the Philippines, with official business address
                at 139 Upper Zone 4, Bulua, Cagayan de Oro City, Misamis Oriental,
                represented herein by its Proprietor,
                <strong>ENGR. WILROSE RUELO DAP-OG</strong>, and Representative,
                <strong>WILFREDO T. RUELO</strong>, hereinafter referred to as the
                <strong>“CONTRACTOR”</strong>;
            </p>

            <div class="legal-party-divider">- and -</div>

            <p class="legal-party-paragraph">
                <strong>{{ strtoupper($contract->client_name) }}</strong>, of legal age,
                Filipino, with address at
                <strong>{{ $contract->client_address ?: '____________________________' }}</strong>,
                hereinafter referred to as the <strong>“OWNER”</strong>;
            </p>

            <p class="legal-opening-title">WITNESSETH: THAT —</p>

            <p>
                <strong>WHEREAS</strong>, the Owner is undertaking a
                <strong>{{ $projectDescription }}</strong> project located at
                <strong>{{ $contract->project_address ?: $contract->client_address }}</strong>,
                hereinafter referred to as the <strong>“Project”</strong>;
            </p>

            <p>
                <strong>WHEREAS</strong>, the Contractor submitted Quotation
                <strong>{{ $contract->quotation->quotation_no }}</strong> to undertake
                the required works for the Project, and after review and agreement,
                both parties accepted the specified scope, pricing, and payment terms;
            </p>

            <p>
                <strong>WHEREAS</strong>, the Contractor represents that it is properly
                equipped, competent, and qualified to perform the required works in
                accordance with approved plans, applicable local regulations, standard
                industry procedures, and the National Plumbing Code of the Philippines;
            </p>

            <p>
                <strong>NOW, THEREFORE</strong>, for and in consideration of the foregoing
                premises and of the mutual covenants, conditions, and stipulations
                stated herein, the parties agree as follows:
            </p>

            <section class="legal-article">
                <h3>ARTICLE I: SCOPE OF WORKS</h3>

                <p>
                    The Contractor shall provide and execute the necessary labor,
                    supervision, tools, equipment, and installations required for the
                    completion of the Project.
                </p>

                <div class="legal-preserved-text">
                    {!! nl2br(e($contract->scope_of_work ?: 'The scope of work shall be based on the approved quotation and project requirements.')) !!}
                </div>

                @if (!empty($contract->special_terms))
                    <p class="legal-special-provision">
                        <strong>Special Provision:</strong>
                        {{ $contract->special_terms }}
                    </p>
                @endif
            </section>
        </div>

        <footer class="legal-page-number">
            Page 1 of 3
        </footer>
    </section>

    <section class="legal-contract-page legal-page-two">
        <div class="legal-document-body">
            <section class="legal-article">
                <h3>ARTICLE II: OWNER’S SUPPLIED MATERIALS (OSM)</h3>

                <p>
                    Unless expressly included in the approved quotation, the physical
                    hardware and fixtures listed below shall be supplied by the Owner.
                    Labor for their installation shall remain under the responsibility
                    of the Contractor when included in the agreed scope:
                </p>

                <ol class="legal-numbered-list">
                    <li>
                        <strong>Equipment</strong>, including water pumps, pressure tanks,
                        storage tanks, and other owner-selected equipment.
                    </li>

                    <li>
                        <strong>Plumbing Fixtures</strong>, including water closets,
                        lavatories, faucets, showers, bidet sprays, accessories, and
                        corresponding trim materials.
                    </li>
                </ol>
            </section>

            <section class="legal-article">
                <h3>ARTICLE III: CONTRACT PRICE</h3>

                <p>
                    For and in consideration of the faithful performance of the
                    above-mentioned works, the Owner agrees to pay the Contractor the
                    total amount of
                    <strong>
                        {{ $amountInWords }} PESOS ONLY
                        (PHP {{ number_format($amount, 2) }})
                    </strong>,
                    subject to the approved quotation, payment schedule, and mutually
                    agreed adjustments.
                </p>
            </section>

            <section class="legal-article">
                <h3>ARTICLE IV: TERMS OF PAYMENT</h3>

                <p>
                    Payment shall be released by the Owner to the Contractor based on
                    the agreed progressive allocations and verified project milestones.
                </p>

                <table class="legal-payment-table">
                    <thead>
                        <tr>
                            <th>Milestone / Payment Phase</th>
                            <th>Percentage Allocation</th>
                            <th>Amount (PHP)</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($paymentPhases as $phase)
                            <tr>
                                <td>{{ $phase['label'] ?? 'Payment Phase' }}</td>
                                <td>{{ number_format((float) ($phase['percent'] ?? 0), 2) }}%</td>
                                <td>PHP {{ number_format((float) ($phase['amount'] ?? 0), 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td>Contract Payment</td>
                                <td>100%</td>
                                <td>PHP {{ number_format($amount, 2) }}</td>
                            </tr>
                        @endforelse

                        <tr class="legal-table-total">
                            <td>TOTAL CONTRACT PRICE</td>
                            <td>100%</td>
                            <td>PHP {{ number_format($amount, 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </section>

            <section class="legal-article">
                <h3>ARTICLE V: WARRANTY AND STANDARDS</h3>

                <p>
                    The Contractor guarantees that all works shall conform to the
                    approved plans, applicable regulations, and standard industry
                    procedures. The Contractor warrants its workmanship against defects
                    or failures for a period of one (1) year from final turnover and
                    acceptance by the Owner.
                </p>

                <p>
                    Defects proven to have resulted from poor workmanship within the
                    warranty period shall be repaired by the Contractor at no additional
                    labor cost to the Owner. Damage caused by misuse, unauthorized
                    alteration, natural deterioration, or materials supplied by the
                    Owner shall not be covered unless otherwise agreed in writing.
                </p>
            </section>

            <section class="legal-article">
                <h3>QUOTATION ITEM BASIS</h3>

                <table class="legal-items-table">
                    <thead>
                        <tr>
                            <th>Description</th>
                            <th>Category</th>
                            <th>Qty</th>
                            <th>Unit Price</th>
                            <th>Total</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($contract->quotation->items as $item)
                            <tr>
                                <td>{{ $item->description }}</td>
                                <td>{{ ucfirst($item->item_category) }}</td>
                                <td>{{ number_format((float) $item->quantity, 2) }}</td>
                                <td>PHP {{ number_format((float) $item->unit_price, 2) }}</td>
                                <td>PHP {{ number_format((float) $item->total_price, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </section>
        </div>

        <footer class="legal-page-number">
            Page 2 of 3
        </footer>
    </section>

    <section class="legal-contract-page legal-page-three">
        <div class="legal-document-body">
            <p class="legal-witness-statement">
                <strong>IN WITNESS WHEREOF</strong>, the parties have signed this
                Agreement on the date and place first written above.
            </p>

            <div class="legal-signature-grid">
                <div class="legal-signature-block">
                    <div class="legal-signature-space"></div>
                    <div class="legal-signature-name">
                        ENGR. WILROSE RUELO DAP-OG
                    </div>
                    <div class="legal-signature-role">
                        Proprietor, WR Plumbing &amp; Construction
                    </div>
                </div>

                <div class="legal-signature-block">
                    <div class="legal-signature-space"></div>
                    <div class="legal-signature-name">
                        {{ strtoupper($contract->client_name) }}
                    </div>
                    <div class="legal-signature-role">
                        Project Owner
                    </div>
                </div>

                <div class="legal-signature-block">
                    <div class="legal-signature-space"></div>
                    <div class="legal-signature-name">
                        WILFREDO T. RUELO
                    </div>
                    <div class="legal-signature-role">
                        Representative, WR Plumbing &amp; Construction
                    </div>
                </div>

                <div class="legal-signature-block">
                    <div class="legal-signature-space"></div>
                    <div class="legal-signature-name">
                        ______________________________
                    </div>
                    <div class="legal-signature-role">
                        Architect / Witness / Noted By
                    </div>
                </div>
            </div>

            <div class="legal-separator"></div>

            <section class="legal-acknowledgement">
                <h3>ACKNOWLEDGEMENT</h3>

                <div class="legal-republic-lines">
                    <div>REPUBLIC OF THE PHILIPPINES )</div>
                    <div>CITY OF CAGAYAN DE ORO&nbsp;&nbsp;&nbsp;&nbsp;) S.S.</div>
                </div>

                <p>
                    BEFORE ME, a Notary Public for and in the City of Cagayan de Oro,
                    this ______ day of ____________________, {{ $contractYear }},
                    personally appeared the signatories above with their valid
                    government-issued identification cards, known to me and to me known
                    to be the same persons who executed the foregoing Contract Agreement,
                    and acknowledged that the same is their free and voluntary act and deed.
                </p>

                <p>
                    This instrument consists of three (3) pages, including this page on
                    which this acknowledgement is written, and has been signed by the
                    parties and their witnesses on each and every page hereof.
                </p>

                <p class="legal-notary-witness">
                    WITNESS MY HAND AND SEAL.
                </p>

                <div class="legal-notary-details">
                    <div>Doc. No. ________;</div>
                    <div>Page No. ________;</div>
                    <div>Book No. ________;</div>
                    <div>Series of {{ $contractYear }}.</div>
                </div>
            </section>
        </div>

        <footer class="legal-page-number">
            Page 3 of 3
        </footer>
    </section>
</article>