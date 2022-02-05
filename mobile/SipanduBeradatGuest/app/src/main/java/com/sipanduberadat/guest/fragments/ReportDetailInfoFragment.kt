package com.sipanduberadat.guest.fragments

import android.os.Bundle
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import androidx.fragment.app.Fragment
import androidx.lifecycle.ViewModelProvider
import com.bumptech.glide.Glide
import com.sipanduberadat.guest.R
import com.sipanduberadat.guest.utils.getDateTime
import com.sipanduberadat.guest.viewModels.ReportDetailViewModel
import kotlinx.android.synthetic.main.layout_report_detail_info.view.*

class ReportDetailInfoFragment: Fragment() {
    override fun onCreateView(
            inflater: LayoutInflater,
            container: ViewGroup?,
            savedInstanceState: Bundle?
    ): View? {
        val view = inflater.inflate(R.layout.layout_report_detail_info, container, false)
        val viewModel = ViewModelProvider(activity!!).get(ReportDetailViewModel::class.java)

        viewModel.report.observe(activity!!, {
            if (it != null) {
                val locationText = "Desa Adat ${it.desa_adat.name}"

                Glide.with(view.context).load(it.masyarakat.avatar).centerCrop().into(view.avatar)
                view.datetime.text = getDateTime(it.time, withSecond = false)
                view.name.text = it.masyarakat.name
                view.location.text = locationText
                view.report_type.text = it.jenis_pelaporan.name

                if (it.title != null && it.photo != null && it.description != null) {
                    view.report_title.text = it.title
                    Glide.with(view.context).load(it.photo).centerCrop().into(view.photo)
                    view.description.text = it.description

                    view.detail_container.visibility = View.VISIBLE
                }
            }
        })

        viewModel.guestReport.observe(activity!!, {
            if (it != null) {
                val locationText = "Desa Adat ${it.desa_adat.name}"

                Glide.with(view.context).load(it.tamu.avatar).centerCrop().into(view.avatar)
                view.datetime.text = getDateTime(it.time, withSecond = false)
                view.name.text = it.tamu.name
                view.location.text = locationText
                view.report_type.text = it.jenis_pelaporan.name

                if (it.title != null && it.photo != null && it.description != null) {
                    view.report_title.text = it.title
                    Glide.with(view.context).load(it.photo).centerCrop().into(view.photo)
                    view.description.text = it.description

                    view.detail_container.visibility = View.VISIBLE
                }
            }
        })

        return view
    }
}