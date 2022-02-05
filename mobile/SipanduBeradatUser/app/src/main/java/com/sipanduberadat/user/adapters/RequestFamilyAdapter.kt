package com.sipanduberadat.user.adapters

import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import androidx.recyclerview.widget.RecyclerView
import com.sipanduberadat.user.R
import com.sipanduberadat.user.models.Kerabat
import com.sipanduberadat.user.models.Me
import com.sipanduberadat.user.services.apis.acceptKerabatAPI
import com.sipanduberadat.user.services.apis.removeKerabatAPI
import com.sipanduberadat.user.viewHolders.RequestFamilyViewHolder
import kotlinx.android.synthetic.main.layout_request_family.view.*

class RequestFamilyAdapter(
        private val view: View,
        private val items: MutableList<Kerabat>,
        private val me: Me
): RecyclerView.Adapter<RequestFamilyViewHolder>() {
    private fun onRequestFinish(response: Any?) {
        if (response == null) {
            return
        }
    }

    private fun onRequestError() {}

    private fun onAccept(id: Long, position: Int) {
        val requestParams = HashMap<String, String>()
        requestParams["id"] = "$id"
        acceptKerabatAPI(view.root, view.context, requestParams, HashMap(), this::onRequestFinish,
                this::onRequestError)

        items.removeAt(position)
        notifyDataSetChanged()

        if (items.isEmpty()) {
            view.recycler_view.visibility = View.GONE
            view.empty_container.visibility = View.VISIBLE
        }
    }

    private fun onDecline(id: Long, position: Int) {
        val requestParams = HashMap<String, String>()
        requestParams["id"] = "$id"
        removeKerabatAPI(view.root, view.context, requestParams, HashMap(), this::onRequestFinish,
                this::onRequestError)

        items.removeAt(position)
        notifyDataSetChanged()

        if (items.isEmpty()) {
            view.recycler_view.visibility = View.GONE
            view.empty_container.visibility = View.VISIBLE
        }
    }

    override fun onCreateViewHolder(parent: ViewGroup, viewType: Int): RequestFamilyViewHolder {
        val v = LayoutInflater.from(view.context).inflate(R.layout.layout_request_family_item, parent,
                false)
        return RequestFamilyViewHolder(v)
    }

    override fun getItemCount(): Int {
        return items.size
    }

    override fun onBindViewHolder(holder: RequestFamilyViewHolder, position: Int) {
        holder.onBindItem(position, items[position], me, this::onDecline, this::onAccept)
    }
}