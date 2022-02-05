package com.sipanduberadat.petugas.fragments

import android.os.Bundle
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import androidx.fragment.app.Fragment
import androidx.lifecycle.ViewModelProvider
import androidx.recyclerview.widget.LinearLayoutManager
import com.google.android.material.chip.Chip
import com.sipanduberadat.petugas.R
import com.sipanduberadat.petugas.adapters.ReportAdapter
import com.sipanduberadat.petugas.dialogs.FilterBottomSheetDialog
import com.sipanduberadat.petugas.models.ReportWrapper
import com.sipanduberadat.petugas.utils.getDate
import com.sipanduberadat.petugas.viewModels.MainViewModel
import com.sipanduberadat.petugas.viewModels.ReportViewModel
import kotlinx.android.synthetic.main.layout_report.view.*

class UnhandleReportFragment: Fragment() {
    private lateinit var reportViewModel: ReportViewModel
    private lateinit var mainViewModel: MainViewModel
    private val reports: MutableList<ReportWrapper> = mutableListOf()
    private val selectedChips: MutableList<Long> = mutableListOf()

    private fun onFilterReport(wrapper: ReportWrapper): Boolean {
        var handledStatus = false
        val reportStatusStatus = if (wrapper.report != null) wrapper.report!!.status == 1 else
            wrapper.guestReport!!.status == 1
        var reportTypeStatus = true
        var reportCategoryStatus = true
        var reporterCategoryStatus = true
        var startDateStatus = true
        var endDateStatus = true

        if (mainViewModel.me.value != null) {
            handledStatus = if (wrapper.report != null) wrapper.report!!.petugas_reports.any {
                it.petugas.id == mainViewModel.me.value!!.id } else
                    wrapper.guestReport!!.petugas_reports.any {
                        it.petugas.id == mainViewModel.me.value!!.id }
        }

        if (selectedChips.size > 0)
            reportTypeStatus = selectedChips.any { if (wrapper.report != null)
                wrapper.report!!.jenis_pelaporan.id == it else
                    wrapper.guestReport!!.jenis_pelaporan.id == it }

        if (reportViewModel.reportCategory.value != null) {
            reportCategoryStatus = when (reportViewModel.reportCategory.value!!) {
                R.id.emergency_chip -> if (wrapper.report != null)
                    wrapper.report!!.jenis_pelaporan.emergency_status else
                        wrapper.guestReport!!.jenis_pelaporan.emergency_status
                R.id.not_emergency_chip -> if (wrapper.report != null)
                    !wrapper.report!!.jenis_pelaporan.emergency_status else
                        !wrapper.guestReport!!.jenis_pelaporan.emergency_status
                else -> true
            }
        }

        if (reportViewModel.reporterCategory.value != null) {
            reporterCategoryStatus = when (reportViewModel.reporterCategory.value!!) {
                R.id.masyarakat_chip -> wrapper.report != null
                R.id.wisatawan_chip -> wrapper.guestReport != null
                else -> true
            }
        }

        if (reportViewModel.startDate.value != null) {
            startDateStatus = getDate(if (wrapper.report != null) wrapper.report!!.time else
                wrapper.guestReport!!.time, withMonthName = false, true) >=
                    getDate(reportViewModel.startDate.value!!.time, withMonthName = false, true)
        }

        if (reportViewModel.endDate.value != null) {
            endDateStatus = getDate(if (wrapper.report != null) wrapper.report!!.time else
                wrapper.guestReport!!.time, withMonthName = false, true) <=
                    getDate(reportViewModel.endDate.value!!.time, withMonthName = false, true)
        }

        return !handledStatus && reportStatusStatus && reportTypeStatus && reportCategoryStatus &&
                reporterCategoryStatus && startDateStatus && endDateStatus
    }

    private fun onInitReport() {
        if (view != null) {
            if (mainViewModel.reports.value != null && mainViewModel.guestReports.value != null) {
                reports.clear()

                mainViewModel.reports.value!!.filter { onFilterReport(ReportWrapper(it, null)) }
                        .map { reports.add(ReportWrapper(it, null)) }
                mainViewModel.guestReports.value!!.filter { onFilterReport(ReportWrapper(null, it)) }
                        .map { reports.add(ReportWrapper(null, it)) }
                reports.sortByDescending { if (it.report != null) it.report!!.time.time else
                    it.guestReport!!.time.time }

                view!!.shimmer_content_container.stopShimmer()
                view!!.shimmer_content_container.visibility = View.GONE

                if (reports.isNotEmpty()) {
                    view!!.empty_container.visibility = View.GONE
                    view!!.recycler_view.visibility = View.VISIBLE
                    view!!.recycler_view.adapter = ReportAdapter(view!!.context, reports,
                        mainViewModel.me.value!!, false)
                } else {
                    view!!.recycler_view.visibility = View.GONE
                    view!!.empty_container.visibility = View.VISIBLE
                }
            } else {
                view!!.empty_container.visibility = View.GONE
                view!!.recycler_view.visibility = View.GONE
                view!!.shimmer_content_container.visibility = View.VISIBLE
                view!!.shimmer_content_container.startShimmer()

                reportViewModel.reportCategory.value = null
                reportViewModel.reporterCategory.value = null
                reportViewModel.reportTypes.value = null
                reportViewModel.startDate.value = null
                reportViewModel.endDate.value = null
            }
        }
    }

    private fun onClickReportTypeChip(id: Long) {
        if (selectedChips.contains(id)) selectedChips.remove(id) else selectedChips.add(id)
        reportViewModel.reportTypes.value = selectedChips
        onInitReport()
    }

    override fun onCreateView(
            inflater: LayoutInflater,
            container: ViewGroup?,
            savedInstanceState: Bundle?
    ): View? {
        val view = inflater.inflate(R.layout.layout_report, container, false)
        reportViewModel = ViewModelProvider(this).get(ReportViewModel::class.java)
        mainViewModel = ViewModelProvider(activity!!).get(MainViewModel::class.java)

        view.recycler_view.layoutManager = LinearLayoutManager(view.context,
                LinearLayoutManager.VERTICAL, false)

        mainViewModel.reportTypes.observe(activity!!, {
            if (it != null) {
                view.shimmer_filter_container.stopShimmer()
                view.shimmer_filter_container.visibility = View.GONE
                view.filter_container.visibility = View.VISIBLE

                for (reportType in it) {
                    val chip = inflater.inflate(R.layout.layout_item_chip, view.report_type_chip_group,
                            false) as Chip
                    chip.text = reportType.name
                    chip.setOnClickListener { onClickReportTypeChip(reportType.id) }
                    view.report_type_chip_group.addView(chip)
                }
            } else {
                selectedChips.clear()
                view.report_type_chip_group.removeAllViews()
                view.filter_container.visibility = View.GONE
                view.shimmer_filter_container.visibility = View.VISIBLE
                view.shimmer_filter_container.startShimmer()
            }
        })

        mainViewModel.reports.observe(activity!!, { onInitReport() })
        mainViewModel.guestReports.observe(activity!!, { onInitReport() })

        view.btn_filter.setOnClickListener {
            FilterBottomSheetDialog {
                selectedChips.clear()
                view.report_type_chip_group.removeAllViews()
                view.filter_container.visibility = View.GONE
                view.shimmer_filter_container.visibility = View.VISIBLE
                view.shimmer_filter_container.startShimmer()
                view.empty_container.visibility = View.GONE
                view.recycler_view.visibility = View.GONE
                view.shimmer_content_container.visibility = View.VISIBLE
                view.shimmer_content_container.startShimmer()

                if (reportViewModel.reportTypes.value != null) selectedChips.addAll(reportViewModel.reportTypes.value!!)
                for (reportType in mainViewModel.reportTypes.value!!) {
                    val chip = inflater.inflate(R.layout.layout_item_chip, view.report_type_chip_group,
                            false) as Chip
                    chip.text = reportType.name
                    chip.isChecked = selectedChips.contains(reportType.id)
                    chip.setOnClickListener { onClickReportTypeChip(reportType.id) }
                    view.report_type_chip_group.addView(chip)
                }

                view.shimmer_filter_container.stopShimmer()
                view.shimmer_filter_container.visibility = View.GONE
                view.filter_container.visibility = View.VISIBLE

                onInitReport()
            }.show(childFragmentManager, tag)
        }

        return view
    }
}