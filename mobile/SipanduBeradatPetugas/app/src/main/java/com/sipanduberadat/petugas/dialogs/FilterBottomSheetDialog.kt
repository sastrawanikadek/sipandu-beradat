package com.sipanduberadat.petugas.dialogs

import android.app.DatePickerDialog
import android.content.DialogInterface
import android.os.Bundle
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import androidx.core.view.children
import androidx.lifecycle.ViewModelProvider
import com.google.android.material.bottomsheet.BottomSheetDialogFragment
import com.google.android.material.chip.Chip
import com.sipanduberadat.petugas.R
import com.sipanduberadat.petugas.utils.getDate
import com.sipanduberadat.petugas.viewModels.MainViewModel
import com.sipanduberadat.petugas.viewModels.ReportViewModel
import kotlinx.android.synthetic.main.bottom_sheet_filter.view.*
import java.util.*

class FilterBottomSheetDialog(
        private val dismissCallback: () -> Unit
): BottomSheetDialogFragment() {
    private lateinit var reportViewModel: ReportViewModel
    private lateinit var mainViewModel: MainViewModel
    private var status: Boolean = false
    private var reportCategory: Int = View.NO_ID
    private var reporterCategory: Int = View.NO_ID
    private var reportTypes: MutableList<Long> = mutableListOf()
    private var startDate: Calendar? = null
    private var endDate: Calendar? = null

    override fun onCreateView(
            inflater: LayoutInflater,
            container: ViewGroup?,
            savedInstanceState: Bundle?
    ): View? {
        val view = inflater.inflate(R.layout.bottom_sheet_filter, container, false)
        reportViewModel = ViewModelProvider(parentFragment!!).get(ReportViewModel::class.java)
        mainViewModel = ViewModelProvider(activity!!).get(MainViewModel::class.java)

        reportCategory = reportViewModel.reportCategory.value ?: reportCategory
        reporterCategory = reportViewModel.reporterCategory.value ?: reporterCategory
        reportTypes = reportViewModel.reportTypes.value ?: reportTypes
        startDate = reportViewModel.startDate.value
        endDate = reportViewModel.endDate.value

        view.report_category_chip_group.check(reportCategory)
        view.reporter_category_chip_group.check(reporterCategory)
        if (startDate != null) view.start_date.setText(getDate(startDate!!.time, false))
        if (endDate != null) view.end_date.setText(getDate(endDate!!.time, false))

        mainViewModel.reportTypes.observe(activity!!, {
            if (it != null) {
                view.report_type_chip_group.removeAllViews()

                for (report in it) {
                    val chip = inflater.inflate(R.layout.layout_item_chip, view.report_type_chip_group,
                            false) as Chip
                    chip.text = report.name
                    chip.isChecked = reportTypes.contains(report.id)
                    chip.setOnClickListener { if (reportTypes.contains(report.id))
                        reportTypes.remove(report.id) else reportTypes.add(report.id) }
                    view.report_type_chip_group.addView(chip)
                }
            }
        })

        view.start_date.setOnClickListener {
            val builder = DatePickerDialog(view.context,
                    {_, year, month, dayOfMonth ->
                        if (startDate == null) startDate = Calendar.getInstance()
                        startDate!!.set(year, month, dayOfMonth)
                        view.start_date.setText(getDate(startDate!!.time, false))
                    },
                    if (startDate != null) startDate!![Calendar.YEAR] else Calendar.getInstance()[Calendar.YEAR],
                    if (startDate != null) startDate!![Calendar.MONTH] else Calendar.getInstance()[Calendar.MONTH],
                    if (startDate != null) startDate!![Calendar.DAY_OF_MONTH] else Calendar.getInstance()[Calendar.DAY_OF_MONTH])
            builder.setButton(DialogInterface.BUTTON_NEUTRAL, "clear") { _, _ ->
                startDate = null
                view.start_date.setText("")
                builder.dismiss()
            }
            builder.datePicker.maxDate = if (endDate != null) endDate!!.timeInMillis else Calendar.getInstance().timeInMillis
            builder.show()
        }

        view.end_date.setOnClickListener {
            val builder = DatePickerDialog(view.context,
                    {_, year, month, dayOfMonth ->
                        if (endDate == null) endDate = Calendar.getInstance()
                        endDate!!.set(year, month, dayOfMonth)
                        view.end_date.setText(getDate(endDate!!.time, false))
                    },
                    if (endDate != null) endDate!![Calendar.YEAR] else Calendar.getInstance()[Calendar.YEAR],
                    if (endDate != null) endDate!![Calendar.MONTH] else Calendar.getInstance()[Calendar.MONTH],
                    if (endDate != null) endDate!![Calendar.DAY_OF_MONTH] else Calendar.getInstance()[Calendar.DAY_OF_MONTH])
            builder.setButton(DialogInterface.BUTTON_NEUTRAL, "clear") { _, _ ->
                endDate = null
                view.end_date.setText("")
                builder.dismiss()
            }
            if (startDate != null) builder.datePicker.minDate = startDate!!.timeInMillis
            builder.datePicker.maxDate = Calendar.getInstance().timeInMillis
            builder.show()
        }

        view.btn_reset.setOnClickListener {
            reportCategory = View.NO_ID
            reporterCategory = View.NO_ID
            reportTypes.clear()
            startDate = null
            endDate = null

            view.report_category_chip_group.clearCheck()
            view.reporter_category_chip_group.clearCheck()

            for (chip in view.report_type_chip_group.children) {
                (chip as Chip).isChecked = false
            }

            view.start_date.setText("")
            view.end_date.setText("")
        }

        view.report_category_chip_group.setOnCheckedChangeListener { _, checkedId -> reportCategory = checkedId}
        view.reporter_category_chip_group.setOnCheckedChangeListener { _, checkedId -> reporterCategory = checkedId }

        view.btn_apply.setOnClickListener {
            reportViewModel.reportCategory.value = reportCategory
            reportViewModel.reporterCategory.value = reporterCategory
            reportViewModel.reportTypes.value = reportTypes
            reportViewModel.startDate.value = startDate
            reportViewModel.endDate.value = endDate
            status = true
            dismiss()
        }

        return view
    }

    override fun onDismiss(dialog: DialogInterface) {
        if (status) dismissCallback()
        super.onDismiss(dialog)
    }
}