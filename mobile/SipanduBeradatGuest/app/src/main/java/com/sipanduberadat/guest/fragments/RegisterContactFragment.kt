package com.sipanduberadat.guest.fragments

import android.os.Bundle
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import android.widget.ArrayAdapter
import androidx.core.widget.addTextChangedListener
import androidx.fragment.app.Fragment
import androidx.lifecycle.ViewModelProvider
import com.sipanduberadat.guest.R
import com.sipanduberadat.guest.adapters.AkomodasiArrayAdapter
import com.sipanduberadat.guest.adapters.NegaraArrayAdapter
import com.sipanduberadat.guest.viewModels.RegisterViewModel
import kotlinx.android.synthetic.main.layout_register_contact.view.*

class RegisterContactFragment: Fragment() {
    private lateinit var viewModel: RegisterViewModel

    override fun onCreateView(
        inflater: LayoutInflater,
        container: ViewGroup?,
        savedInstanceState: Bundle?
    ): View? {
        val view = inflater.inflate(R.layout.layout_register_contact, container, false)
        viewModel = ViewModelProvider(activity!!).get(RegisterViewModel::class.java)

        view.email_edit_text.addTextChangedListener { viewModel.email.value = "$it" }
        view.phone_edit_text.addTextChangedListener { viewModel.phone.value = "$it" }
        view.country_auto_complete.setAdapter(NegaraArrayAdapter(view.context, viewModel.countries.value!!))
        view.accommodation_auto_complete.setAdapter(AkomodasiArrayAdapter(view.context, viewModel.accommodations.value!!))
        view.country_auto_complete.setOnItemClickListener { adapterView, _, pos, _ ->
            viewModel.country.value = adapterView.getItemIdAtPosition(pos)
        }
        view.accommodation_auto_complete.setOnItemClickListener { adapterView, _, pos, _ ->
            viewModel.accommodation.value = adapterView.getItemIdAtPosition(pos)
        }

        return view
    }
}