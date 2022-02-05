package com.sipanduberadat.user.fragments

import android.app.Activity
import android.app.DatePickerDialog
import android.app.TimePickerDialog
import android.content.Intent
import android.graphics.Bitmap
import android.os.Bundle
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import androidx.fragment.app.Fragment
import androidx.lifecycle.ViewModelProvider
import com.bumptech.glide.Glide
import com.google.android.material.snackbar.Snackbar
import com.sipanduberadat.user.R
import com.sipanduberadat.user.utils.choosePhoto
import com.sipanduberadat.user.utils.getDateTime
import com.sipanduberadat.user.utils.snackbarWarning
import com.sipanduberadat.user.viewModels.BlockedRoadViewModel
import kotlinx.android.synthetic.main.layout_blocked_road_info.view.*
import kotlinx.android.synthetic.main.layout_blocked_road_info.view.empty_photo
import kotlinx.android.synthetic.main.layout_blocked_road_info.view.photo
import java.io.ByteArrayOutputStream
import java.util.*

class BlockedRoadInfoFragment: Fragment() {
    private lateinit var viewModel: BlockedRoadViewModel

    private fun onNext() {
        view!!.title_input_layout.helperText = ""
        view!!.start_time_input_layout.helperText = ""
        view!!.end_time_input_layout.helperText = ""

        val title = "${view!!.title_edit_text.text}"
        val startTime = viewModel.startTimeDate.value
        val endTime = viewModel.endTimeDate.value

        if (title.isBlank()) {
            view!!.title_input_layout.helperText = "Judul tidak boleh kosong"
            view!!.title_input_layout.requestFocus()
            return
        }

        if (startTime == null || startTime.timeInMillis < Calendar.getInstance().timeInMillis) {
            view!!.start_time_input_layout.helperText = "Waktu mulai harus valid dan tidak boleh kosong"
            view!!.start_time_input_layout.requestFocus()
            return
        }

        if (endTime == null || endTime.timeInMillis < Calendar.getInstance().timeInMillis) {
            view!!.end_time_input_layout.helperText = "Waktu selesai harus valid dan tidak boleh kosong"
            view!!.end_time_input_layout.requestFocus()
            return
        }

        val oneHourBeforeEnd = Calendar.getInstance().apply {
            time = endTime.time
            add(Calendar.HOUR, -1)
            add(Calendar.SECOND, 1)
        }

        if (startTime.timeInMillis > oneHourBeforeEnd.timeInMillis) {
            view!!.start_time_input_layout.helperText = "Jangka waktu minimal 1 jam"
            view!!.start_time_input_layout.requestFocus()
            return
        }

        if (viewModel.cover.value == null) {
            snackbarWarning(view!!, "Foto tidak boleh kosong", Snackbar.LENGTH_LONG).show()
            return
        }

        viewModel.title.value = title
        viewModel.currentPage.value = viewModel.currentPage.value!! + 1
    }

    override fun onCreateView(
            inflater: LayoutInflater,
            container: ViewGroup?,
            savedInstanceState: Bundle?
    ): View? {
        val view = inflater.inflate(R.layout.layout_blocked_road_info, container, false)
        viewModel = ViewModelProvider(activity!!).get(BlockedRoadViewModel::class.java)

        view.empty_photo.setOnClickListener {
            val intentChooser = choosePhoto()
            startActivityForResult(intentChooser, 1)
        }

        view.photo.setOnClickListener {
            val intentChooser = choosePhoto()
            startActivityForResult(intentChooser, 1)
        }

        view.start_time_edit_text.setOnClickListener {
            val calendar = if (viewModel.startTimeDate.value != null)
                viewModel.startTimeDate.value!! else Calendar.getInstance()

            DatePickerDialog(view.context,
                { _, year, month, dayOfMonth ->
                    TimePickerDialog(view.context,
                            { _, hour, minute ->
                                viewModel.startTimeDate.value = Calendar.getInstance().apply {
                                    set(year, month, dayOfMonth, hour, minute, 0)
                                }
                                viewModel.startTime.value = getDateTime(viewModel.startTimeDate.value!!.time,
                                        withMonthName = false, withLocale = false, yearFirst = true)
                                view.start_time_edit_text.setText(viewModel.startTime.value)
                            },
                            calendar[Calendar.HOUR_OF_DAY],
                            calendar[Calendar.MINUTE],
                            true).show()
                },
                calendar[Calendar.YEAR],
                calendar[Calendar.MONTH],
                calendar[Calendar.DAY_OF_MONTH]).apply {
                datePicker.minDate = Calendar.getInstance().timeInMillis
                if (viewModel.endTimeDate.value != null) datePicker.maxDate = viewModel.endTimeDate.value!!.timeInMillis
                show()
            }
        }

        view.end_time_edit_text.setOnClickListener {
            val calendar = if (viewModel.endTimeDate.value != null)
                viewModel.endTimeDate.value!! else Calendar.getInstance()

            DatePickerDialog(view.context,
                    { _, year, month, dayOfMonth ->
                        TimePickerDialog(view.context,
                                { _, hour, minute ->
                                    viewModel.endTimeDate.value = Calendar.getInstance().apply {
                                        set(year, month, dayOfMonth, hour, minute, 0)
                                    }
                                    viewModel.endTime.value = getDateTime(viewModel.endTimeDate.value!!.time,
                                            withMonthName = false, withLocale = false, yearFirst = true)
                                    view.end_time_edit_text.setText(viewModel.endTime.value)
                                },
                                calendar[Calendar.HOUR_OF_DAY],
                                calendar[Calendar.MINUTE],
                                true).show()
                    },
                    calendar[Calendar.YEAR],
                    calendar[Calendar.MONTH],
                    calendar[Calendar.DAY_OF_MONTH]).apply {
                datePicker.minDate = viewModel.startTimeDate.value!!.timeInMillis
                show()
            }
        }

        view.btn_back.setOnClickListener { activity!!.finish() }
        view.btn_next.setOnClickListener { onNext() }
        return view
    }

    override fun onActivityResult(requestCode: Int, resultCode: Int, data: Intent?) {
        if (resultCode == Activity.RESULT_OK) {
            if (data != null) {
                if (requestCode == 1) {
                    if (data.data != null) {
                        val uri = data.data
                        viewModel.cover.value = view!!.context.contentResolver
                                .openInputStream(uri!!)?.buffered()?.use { it.readBytes() }

                        Glide.with(view!!.context).load(uri).centerCrop().into(view!!.photo)
                        view!!.empty_photo.visibility = View.GONE
                        view!!.photo.visibility = View.VISIBLE
                        return
                    }

                    val bitmap = data.extras!!.get("data") as Bitmap
                    val stream = ByteArrayOutputStream()
                    bitmap.compress(Bitmap.CompressFormat.JPEG, 100, stream)
                    viewModel.cover.value = stream.toByteArray()

                    Glide.with(view!!.context).load(bitmap).centerCrop().into(view!!.photo)
                    view!!.empty_photo.visibility = View.GONE
                    view!!.photo.visibility = View.VISIBLE
                }
            }
        }
    }
}