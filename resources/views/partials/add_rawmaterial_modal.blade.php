<div class="modal fade" id="add-rawmaterial-modal-{{ $detail->id }}" tabindex="-1" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Add Raw Material</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="POST" action="{{ route('save.rawmaterial', $detail->id) }}">
                    @csrf
                    <div class="mb-3">
                        <input type="hidden" name="detail_id" value="{{ $detail->id }}">
                        <!-- Include the detail ID -->
                        <span>Select Raw Material </span>
                        <select name="MasterId" class="form-select">
                            @foreach ($masterDataCollection as $masterData)
                                @if ($masterData->fg_code == $final)
                                    <option value="{{ $masterData->id }}">{{ $masterData->rm_code }} (Quantity:
                                        {{ $masterData->rm_quantity }})(ID: {{ $masterData->id }})</option>
                                    @php
                                        $master = $masterData->id;
                                    @endphp
                                @endif
                            @endforeach
                            @if (!$masterData->fg_code)
                                <option value="" disabled>No raw materials available</option>
                            @endif
                        </select>
                        <input type="hidden" name="header_id" value="{{ $found->id ?? '' }}">
                        <input type="hidden" name="report_id" value="{{ $found->report_id ?? '' }}">
                    </div>

                    <div class="mb-3">
                        <label for="rm_warehouse">RM Warehouse</label>

                        <select id="rm_warehouse" name="rm_warehouse" class="form-select">
                            <option value="01">01</option>
                            <option value="CFC">CFC</option>
                            <option value="CMS">CMS</option>
                            <option value="CMSO">CMSO</option>
                            <option value="FFA">FFA</option>
                            <option value="FFI">FFI</option>
                            <option value="FFM">FFM</option>
                            <option value="FFS">FFS</option>
                            <option value="FG">FG</option>
                            <option value="FT">FT</option>
                            <option value="IN6">IN6</option>
                            <option value="IND">IND</option>
                            <option value="KRCMS">KRCMS</option>
                            <option value="KRFG">KRFG</option>
                            <option value="KRRJCT">KRRJCT</option>
                            <option value="KRRM">KRRM</option>
                            <option value="KRWIP">KRWIP</option>
                            <option value="MLD">MLD</option>
                            <option value="MLDCPG">MLDCPG</option>
                            <option value="QCFT">QCFT</option>
                            <option value="QCRM">QCRM</option>
                            <option value="RFA">RFA</option>
                            <option value="RFI">RFI</option>
                            <option value="RFM">RFM</option>
                            <option value="RFS">RFS</option>
                            <option value="RJCT">RJCT</option>
                            <option value="RM">RM</option>
                            <option value="RMC">RMC</option>
                            <option value="RYCL">RYCL</option>
                            <option value="SMP">SMP</option>
                            <option value="SUB-F">SUB-F</option>
                            <option value="SUB-W">SUB-W</option>
                            <option value="WFA">WFA</option>
                            <option value="WFI">WFI</option>
                            <option value="WFM">WFM</option>
                            <option value="WFS">WFS</option>
                            <option value="WIP">WIP</option>
                            <option value="WOS">WOS</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary">Save</button>
            </div>
        </div>
    </div>
</div>
